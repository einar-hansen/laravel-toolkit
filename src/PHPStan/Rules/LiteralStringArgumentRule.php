<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * A parameter declaring `@param literal-string $x` must be given one.
 *
 * @implements Rule<CallLike>
 */
final readonly class LiteralStringArgumentRule implements Rule
{
    /**
     * @param  list<string>  $namespacePrefixes  only methods declared in one of these
     *                                           namespaces are checked
     */
    public function __construct(private array $namespacePrefixes = []) {}

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

        $method = $this->resolveMethod($node, $scope);
        if (! $method instanceof ExtendedMethodReflection) {
            return [];
        }

        if (! $this->isFirstParty($method->getDeclaringClass()->getName())) {
            return [];
        }

        if ($node->isFirstClassCallable()) {
            return [];
        }

        $args = $node->getArgs();
        $parameters = ParametersAcceptorSelector::selectFromArgs($scope, $args, $method->getVariants())
            ->getParameters();

        $errors = [];

        foreach ($args as $position => $arg) {
            if ($arg->unpack) {
                continue;
            }

            $parameter = $arg->name instanceof Identifier
                ? array_find($parameters, fn ($candidate): bool => $candidate->getName() === $arg->name->toString())
                : ($parameters[$position] ?? null);
            if ($parameter === null) {
                continue;
            }

            if (! $parameter->getType()->isLiteralString()->yes()) {
                continue;
            }

            $given = $scope->getType($arg->value);
            if ($given->isLiteralString()->yes()) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Parameter $%s of %s::%s() expects literal-string, %s given. The annotation is '
                    .'a guarantee that the value comes from source code rather than from a '
                    .'request; passing a plain string silently breaks it.',
                $parameter->getName(),
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $given->describe(VerbosityLevel::typeOnly()),
            ))
                ->identifier('toolkit.literalString.argument')
                ->line($arg->value->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function isFirstParty(string $class): bool
    {
        return array_any($this->namespacePrefixes, fn (string $prefix): bool => str_starts_with($class, $prefix));
    }

    private function resolveMethod(MethodCall|StaticCall $call, Scope $scope): ?ExtendedMethodReflection
    {
        if (! $call->name instanceof Identifier) {
            return null;
        }

        $name = $call->name->toString();

        $calledOn = $call instanceof MethodCall
            ? $scope->getType($call->var)
            : ($call->class instanceof Name ? $scope->resolveTypeByName($call->class) : $scope->getType($call->class));

        return $calledOn->hasMethod($name)->yes() ? $calledOn->getMethod($name, $scope) : null;
    }
}
