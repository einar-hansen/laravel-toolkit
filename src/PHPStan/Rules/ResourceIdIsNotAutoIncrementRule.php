<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use Illuminate\Http\Resources\Json\JsonResource;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeCombinator;

/**
 * An API payload must not carry an auto-increment primary key. Identify a
 *
 * @implements Rule<ArrayItem>
 */
final class ResourceIdIsNotAutoIncrementRule implements Rule
{
    private const string JSON_RESOURCE = JsonResource::class;

    private const string PAYLOAD_METHOD = 'toArray';

    public function getNodeType(): string
    {
        return ArrayItem::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof ArrayItem) {
            return [];
        }

        if ($scope->getFunctionName() !== self::PAYLOAD_METHOD) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection || ! $classReflection->isSubclassOf(self::JSON_RESOURCE)) {
            return [];
        }

        if (! $node->key instanceof String_) {
            return [];
        }

        $key = $node->key->value;

        if (! $this->readsLikeAnIdentifier($key)) {
            return [];
        }

        // Nullable is the same exposure — the non-null branch is still the
        // sequence value.
        if (! TypeCombinator::removeNull($scope->getType($node->value))->isInteger()->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                "Resource exposes `%s` as an integer identifier. Use the record's `ulid` or `slug` "
                    .'to follow the public-identifier policy. Keep authorization checks in place.',
                $key,
            ))
                ->identifier('toolkit.resource.autoIncrementIdExposed')
                ->build(),
        ];
    }

    private function readsLikeAnIdentifier(string $key): bool
    {
        return $key === 'id' || str_ends_with($key, '_id');
    }
}
