<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use Illuminate\Mail\Mailable;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Every concrete Mailable must render in the recipient's language. Two checks:
 *
 * @implements Rule<InClassNode>
 */
final readonly class MailableLocaleRule implements Rule
{
    /**
     * @param  list<class-string>  $localeAwareTraits
     * @param  list<string>  $localeMethods
     * @param  list<string>  $excludedNamespaces  class-name prefixes the rule ignores
     */
    public function __construct(
        private array $excludedNamespaces = [],
        private array $localeAwareTraits = [],
        private array $localeMethods = ['locale'],
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $reflection = $node->getClassReflection();
        $classNode = $node->getOriginalNode();

        if (! $classNode instanceof Class_ || $classNode->isAbstract() || $reflection->isAnonymous()) {
            return [];
        }

        if (! $reflection->isSubclassOf(Mailable::class)) {
            return [];
        }

        $className = $reflection->getName();

        foreach ($this->excludedNamespaces as $prefix) {
            if (str_starts_with($className, $prefix)) {
                return [];
            }
        }

        $errors = [];
        $finder = new NodeFinder;

        if (! $this->choosesLocale($reflection->getTraits(true), $classNode, $finder)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Mailable %s never picks a locale. Call $this->locale() or use a configured locale-aware trait so the mail renders in the recipient\'s language.',
                $className,
            ))
                ->identifier('toolkit.mail.noLocale')
                ->line($classNode->getStartLine())
                ->build();
        }

        foreach ($classNode->getMethods() as $method) {
            foreach ($this->subjectExpressions($method, $finder) as $expr) {
                $literal = $this->literalCopy($expr);

                if ($literal === null) {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Hardcoded subject "%s" in %s. Use __(\'emails.<mail>.subject\', [], $this->locale) so the subject follows the recipient\'s locale.',
                    $literal,
                    $className,
                ))
                    ->identifier('toolkit.mail.hardcodedSubject')
                    ->line($expr->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $traits  keyed by trait class name
     */
    private function choosesLocale(array $traits, Class_ $class, NodeFinder $finder): bool
    {
        if (array_intersect($this->localeAwareTraits, array_keys($traits)) !== []) {
            return true;
        }

        return $finder->findFirst($class->stmts, fn (Node $n): bool => $n instanceof MethodCall
            && $n->var instanceof Variable
            && $n->var->name === 'this'
            && $n->name instanceof Identifier
            && in_array($n->name->toString(), $this->localeMethods, true)) instanceof Node;
    }

    /**
     * Every expression that ends up as the subject inside one method: the
     * `subject:` argument of `new Envelope(...)`, the argument of
     * `$this->subject(...)`, and — when either is a local variable — every
     * assignment to that variable in the same method.
     *
     * @return list<Expr>
     */
    private function subjectExpressions(ClassMethod $method, NodeFinder $finder): array
    {
        if ($method->stmts === null) {
            return [];
        }

        $direct = [];

        foreach ($finder->findInstanceOf($method->stmts, New_::class) as $new) {
            if (! $new->class instanceof Name) {
                continue;
            }

            if ($new->class->getLast() !== 'Envelope') {
                continue;
            }

            foreach ($new->args as $arg) {
                if ($arg instanceof Arg && $arg->name instanceof Identifier && $arg->name->toString() === 'subject') {
                    $direct[] = $arg->value;
                }
            }
        }

        foreach ($finder->findInstanceOf($method->stmts, MethodCall::class) as $call) {
            if (
                $call->var instanceof Variable
                && $call->var->name === 'this'
                && $call->name instanceof Identifier
                && $call->name->toString() === 'subject'
                && isset($call->args[0])
                && $call->args[0] instanceof Arg
            ) {
                $direct[] = $call->args[0]->value;
            }
        }

        $resolved = [];

        foreach ($direct as $expr) {
            if (! $expr instanceof Variable || ! is_string($expr->name)) {
                $resolved[] = $expr;

                continue;
            }

            $name = $expr->name;

            foreach ($finder->findInstanceOf($method->stmts, Assign::class) as $assign) {
                if ($assign->var instanceof Variable && $assign->var->name === $name) {
                    $resolved[] = $assign->expr;
                }
            }
        }

        return $resolved;
    }

    /**
     * The first piece of literal, letter-bearing text inside an expression —
     * null when the expression carries none. Punctuation-only literals
     * (' – ', ': ') pass so `__('…').' – '.$name` stays legal; sprintf's
     * format string counts because it is the copy.
     */
    private function literalCopy(Expr $expr): ?string
    {
        if ($expr instanceof String_) {
            return preg_match('/\p{L}/u', $expr->value) === 1 ? $expr->value : null;
        }

        if ($expr instanceof InterpolatedString) {
            foreach ($expr->parts as $part) {
                if ($part instanceof InterpolatedStringPart && preg_match('/\p{L}/u', $part->value) === 1) {
                    return $part->value;
                }
            }

            return null;
        }

        if ($expr instanceof Concat) {
            return $this->literalCopy($expr->left) ?? $this->literalCopy($expr->right);
        }

        if ($expr instanceof Ternary) {
            return ($expr->if instanceof Expr ? $this->literalCopy($expr->if) : null) ?? $this->literalCopy($expr->else);
        }

        if ($expr instanceof FuncCall && $expr->name instanceof Name && $expr->name->toString() === 'sprintf' && isset($expr->args[0]) && $expr->args[0] instanceof Arg) {
            return $this->literalCopy($expr->args[0]->value);
        }

        return null;
    }
}
