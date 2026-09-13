<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\PhpDoc\Tag\PropertyTag;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;

/**
 * Every concrete `JsonResource` subclass must carry a class-level
 *
 * @implements Rule<InClassNode>
 */
final class JsonResourceAnnotationRule implements Rule
{
    private const string JSON_RESOURCE = JsonResource::class;

    private const string RESOURCE_COLLECTION = ResourceCollection::class;

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof InClassNode) {
            return [];
        }

        $classReflection = $node->getClassReflection();

        if ($classReflection->isAnonymous() || $classReflection->isAbstract()) {
            return [];
        }

        if (! $classReflection->isSubclassOf(self::JSON_RESOURCE)) {
            return [];
        }

        if ($classReflection->isSubclassOf(self::RESOURCE_COLLECTION)) {
            return [];
        }

        $propertyTag = $this->findResourcePropertyTag($classReflection);

        if (! $propertyTag instanceof PropertyTag) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '%s extends JsonResource without a `@property <Model> $resource` docblock, '
                        .'so every $this->resource->... read in it is unchecked. Add the tag naming '
                        .'the model it wraps — `@mixin` does not count, it types $this, not $resource.',
                    $classReflection->getDisplayName(),
                ))
                    ->identifier('toolkit.jsonResource.missingResourceAnnotation')
                    ->build(),
            ];
        }

        if ($propertyTag->getReadableType() instanceof MixedType) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '%s declares `@property mixed $resource`, which types nothing. Name the model '
                        .'(or the array shape / DTO) the resource actually wraps.',
                    $classReflection->getDisplayName(),
                ))
                    ->identifier('toolkit.jsonResource.mixedResourceAnnotation')
                    ->build(),
            ];
        }

        return [];
    }

    /**
     * Walks the class and its ancestors up to — but not including —
     * JsonResource itself, so a subclass of an already-annotated resource
     * inherits the tag instead of being asked to repeat it.
     */
    private function findResourcePropertyTag(ClassReflection $classReflection): ?PropertyTag
    {
        $current = $classReflection;

        while ($current instanceof ClassReflection && $current->getName() !== self::JSON_RESOURCE) {
            $propertyTag = $current->getResolvedPhpDoc()?->getPropertyTags()['resource'] ?? null;

            if ($propertyTag !== null) {
                return $propertyTag;
            }

            $current = $current->getParentClass();
        }

        return null;
    }
}
