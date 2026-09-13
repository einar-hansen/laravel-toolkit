<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Inside a `JsonResource`, `$this->email` is not a property read — it falls
 *
 * @implements Rule<PropertyFetch>
 */
final class JsonResourceMagicPropertyRule implements Rule
{
    private const string JSON_RESOURCE = 'Illuminate\Http\Resources\Json\JsonResource';

    public function getNodeType(): string
    {
        return PropertyFetch::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof PropertyFetch) {
            return [];
        }

        if (! $node->var instanceof Variable || $node->var->name !== 'this') {
            return [];
        }

        // `$this->{$column}` — the name isn't known statically, so there is
        // nothing to check and nothing to suggest.
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection || ! $classReflection->isSubclassOf(self::JSON_RESOURCE)) {
            return [];
        }

        $propertyName = $node->name->toString();

        if ($classReflection->hasNativeProperty($propertyName)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '$this->%1$s reads through JsonResource::__get() and is not checked against the '
                    .'wrapped model. Use $this->resource->%1$s.',
                $propertyName,
            ))
                ->identifier('toolkit.jsonResource.magicPropertyAccess')
                ->build(),
        ];
    }
}
