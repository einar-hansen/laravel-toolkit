<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use Illuminate\Support\Sleep;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FunctionCallableNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Prefer Laravel's fakeable Sleep helper over native delays.
 *
 * @implements Rule<Expr>
 */
final readonly class PreferSleepRule implements Rule
{
    public function __construct(private ReflectionProvider $reflectionProvider) {}

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // @phpstan-ignore phpstanApi.instanceofAssumption (Unwrap PHPStan's virtual first-class callable node; covered by a regression fixture.)
        if ($node instanceof FunctionCallableNode) {
            $node = $node->getOriginalNode();
        }

        if (! $node instanceof FuncCall || ! $node->name instanceof Name) {
            return [];
        }

        // Resolve aliases and namespace fallback instead of matching source spelling.
        $name = $this->reflectionProvider->resolveFunctionName($node->name, $scope);
        if ($name === null) {
            return [];
        }

        $name = mb_strtolower($name);
        if (! in_array($name, ['sleep', 'usleep'], true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Use '.Sleep::class.'::%s() instead of %s() so tests can fake delays with Sleep::fake().',
                $name,
                $name,
            ))
                ->identifier('toolkit.sleep.nativeCall')
                ->build(),
        ];
    }
}
