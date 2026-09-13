<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * The SQL string handed to a raw query method must be provably built from
 *
 * @implements Rule<CallLike>
 */
final readonly class RawSqlNoInterpolationRule implements Rule
{
    /**
     * Query-builder methods whose first argument is raw SQL. These names are
     * distinctive enough to match without a receiver type check, which
     * matters: half of them are reached through a closure parameter that
     * analyses as `mixed` (`fn ($q) => $q->whereRaw(...)`), and requiring a
     * type would silently skip exactly those call sites.
     *
     * @var list<string>
     */
    private const array RAW_BUILDER_METHODS = [
        'crossJoinRaw',
        'fromRaw',
        'groupByRaw',
        'havingRaw',
        'joinRaw',
        'leftJoinRaw',
        'orderByRaw',
        'orHavingRaw',
        'orWhereRaw',
        'rightJoinRaw',
        'selectRaw',
        'whereRaw',
    ];

    /**
     * Connection methods whose first argument is raw SQL. `select`, `update`
     * and `delete` are also ordinary query-builder methods that take columns
     * or an attribute array, so these only count on the DB facade or on
     * something typed as a Connection.
     *
     * @var list<string>
     */
    private const array RAW_CONNECTION_METHODS = [
        'cursor',
        'delete',
        'insert',
        'raw',
        'scalar',
        'select',
        'selectOne',
        'statement',
        'unprepared',
        'update',
    ];

    private const string DB_FACADE = 'Illuminate\Support\Facades\DB';

    private const string CONNECTION = 'Illuminate\Database\ConnectionInterface';

    /**
     * @param  list<string>  $safeCalls  `Class::method` static calls whose result may be
     *                                   embedded in SQL, for what a placeholder cannot
     *                                   express. Each one guarantees its own output.
     * @param  list<string>  $excludedPaths  path fragments the rule does not apply to
     */
    public function __construct(
        private array $safeCalls = [],
        private array $excludedPaths = [],
    ) {}

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return [];
        }

        if ($this->isExcluded($scope->getFile())) {
            return [];
        }

        $method = $this->methodName($node);
        if ($method === null) {
            return [];
        }

        if (! $this->isRawSqlCall($node, $method, $scope)) {
            return [];
        }

        if ($node->isFirstClassCallable()) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $sqlArgument = array_find($args, fn ($arg): bool => $arg->name instanceof Identifier
            && in_array($arg->name->toString(), ['sql', 'query', 'expression'], true));
        $sqlArgument ??= $args[0]->name === null && ! $args[0]->unpack ? $args[0] : null;
        if ($sqlArgument === null) {
            return [];
        }

        $sql = $sqlArgument->value;
        $culprit = $this->firstUnsafeOperand($sql, $scope);

        if (! $culprit instanceof Expr) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s() builds its SQL from a value that is not provably literal (%s). '
                    .'Pass the value as a binding — %s(\'... = ?\', [$value]). If it is a SQL '
                    .'fragment rather than a value, annotate whatever produces it '
                    .'`@return literal-string`; if it is an identifier, which cannot be a '
                    .'placeholder, use a validated identifier helper registered in safeCalls.',
                $method,
                $scope->getType($culprit)->describe(VerbosityLevel::typeOnly()),
                $method,
            ))
                ->identifier('toolkit.sql.rawInterpolation')
                ->line($sql->getStartLine())
                ->build(),
        ];
    }

    private function isExcluded(string $file): bool
    {
        return array_any($this->excludedPaths, fn ($excludedPath): bool => str_contains($file, $excludedPath));
    }

    private function methodName(MethodCall|StaticCall $call): ?string
    {
        return $call->name instanceof Identifier ? $call->name->toString() : null;
    }

    private function isRawSqlCall(MethodCall|StaticCall $call, string $method, Scope $scope): bool
    {
        if (in_array($method, self::RAW_BUILDER_METHODS, true)) {
            return true;
        }

        if (! in_array($method, self::RAW_CONNECTION_METHODS, true)) {
            return false;
        }

        if ($call instanceof StaticCall) {
            return $call->class instanceof Name
                && $scope->resolveName($call->class) === self::DB_FACADE;
        }

        $receiver = $scope->getType($call->var);

        // `never` is a subtype of everything, so an unreachable call would
        // otherwise read as a Connection and get reported — `$model->update()`
        // in a branch PHPStan proved dead is not raw SQL.
        if ($receiver instanceof NeverType) {
            return false;
        }

        return new ObjectType(self::CONNECTION)->isSuperTypeOf($receiver)->yes();
    }

    private function isSafeType(Type $type): bool
    {
        if ($type->isLiteralString()->yes()) {
            return true;
        }

        if ($type->isInteger()->yes()) {
            return true;
        }

        if ($type->isFloat()->yes()) {
            return true;
        }

        if ($type->isBoolean()->yes()) {
            return true;
        }

        return $type->isNumericString()->yes();
    }

    private function isSafeCall(Expr $expr, Scope $scope): bool
    {
        if (! $expr instanceof StaticCall || ! $expr->class instanceof Name) {
            return false;
        }

        $method = $this->methodName($expr);
        if ($method === null) {
            return false;
        }

        return in_array($scope->resolveName($expr->class).'::'.$method, $this->safeCalls, true);
    }

    /**
     * The operand the message should describe, or null when the whole
     * expression is safe.
     *
     * Concat, interpolation and `sprintf()` are walked structurally rather
     * than judged by their result type, for two different reasons. Gluing a
     * literal to a `string` yields plain `string`, so a type-only verdict
     * would blame the whole expression instead of the one operand at fault.
     * And `sprintf()` drops the `literal-string` accessory type even when
     * every argument carries it, so a type-only verdict would reject safe
     * formatting outright; its output is a function of a constant format and
     * its arguments, so checking those is both sound and usable.
     */
    private function firstUnsafeOperand(Expr|InterpolatedStringPart $expr, Scope $scope): ?Expr
    {
        if ($expr instanceof InterpolatedStringPart) {
            return null;
        }

        if ($expr instanceof Concat) {
            return $this->firstUnsafeOperand($expr->left, $scope)
                ?? $this->firstUnsafeOperand($expr->right, $scope);
        }

        if ($expr instanceof InterpolatedString) {
            foreach ($expr->parts as $part) {
                $unsafe = $this->firstUnsafeOperand($part, $scope);
                if ($unsafe instanceof Expr) {
                    return $unsafe;
                }
            }

            return null;
        }

        if ($expr instanceof FuncCall && ! $expr->isFirstClassCallable() && $this->isConstantFormatSprintf($expr, $scope)) {
            foreach ($expr->getArgs() as $arg) {
                $unsafe = $this->firstUnsafeOperand($arg->value, $scope);
                if ($unsafe instanceof Expr) {
                    return $unsafe;
                }
            }

            return null;
        }

        if ($this->isSafeCall($expr, $scope)) {
            return null;
        }

        return $this->isSafeType($scope->getType($expr)) ? null : $expr;
    }

    /**
     * A dynamic format string is itself a way to smuggle SQL in, so only a
     * constant one earns the structural walk; anything else falls through to
     * the type check.
     */
    private function isConstantFormatSprintf(FuncCall $expr, Scope $scope): bool
    {
        if (! $expr->name instanceof Name || $expr->name->toLowerString() !== 'sprintf') {
            return false;
        }

        $args = $expr->getArgs();

        return $args !== [] && $scope->getType($args[0]->value)->getConstantStrings() !== [];
    }
}
