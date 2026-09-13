<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * The `__call` half of {@see JsonResourceMagicPropertyRule}: inside a
 *
 * @implements Rule<MethodCall>
 */
final class JsonResourceMagicMethodRule implements Rule
{
    private const string JSON_RESOURCE = 'Illuminate\Http\Resources\Json\JsonResource';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof MethodCall) {
            return [];
        }

        if (! $node->var instanceof Variable || $node->var->name !== 'this') {
            return [];
        }

        if (! $node->name instanceof Identifier) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection || ! $classReflection->isSubclassOf(self::JSON_RESOURCE)) {
            return [];
        }

        $methodName = $node->name->toString();

        if ($classReflection->hasNativeMethod($methodName)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '$this->%1$s() is forwarded by JsonResource::__call() and is not checked against '
                    .'the wrapped model. Use $this->resource->%1$s().',
                $methodName,
            ))
                ->identifier('toolkit.jsonResource.magicMethodCall')
                ->build(),
        ];
    }
}
