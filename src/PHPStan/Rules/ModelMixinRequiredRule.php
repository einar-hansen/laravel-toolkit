<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Every Eloquent model must carry `@mixin IdeHelper<ClassName>` on its class
 *
 * @implements Rule<InClassNode>
 */
final class ModelMixinRequiredRule implements Rule
{
    private const string MODEL = Model::class;

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

        if ($classReflection->isAnonymous()) {
            return [];
        }

        if (! $classReflection->isSubclassOf(self::MODEL)) {
            return [];
        }

        $expectedMixin = 'IdeHelper'.$this->shortName($classReflection->getName());
        $docComment = $node->getOriginalNode()->getDocComment()?->getText() ?? '';

        if ($this->declaresMixin($docComment, $expectedMixin)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s is an Eloquent model without `@mixin %s` on its class docblock. The configured IDE Helper policy '
                    .'requires this link to the generated model annotations. '
                    .'Add the line, then run `php artisan ide-helper:models --write-mixin` to generate the helper class '
                    .'it points at.',
                $classReflection->getDisplayName(),
                $expectedMixin,
            ))
                ->identifier('toolkit.model.missingIdeHelperMixin')
                ->build(),
        ];
    }

    /**
     * Matches `@mixin IdeHelperFoo`, with or without a leading backslash, and
     * only as a whole word — so `IdeHelperUser` never satisfies a model that
     * should declare `IdeHelperUserProfile`. A wrong-but-plausible name is the
     * failure this catches: it is what a copy-pasted docblock leaves behind,
     * and it types the model against a different table entirely.
     */
    private function declaresMixin(string $docComment, string $expectedMixin): bool
    {
        return preg_match('/@mixin\s+\\\\?'.preg_quote($expectedMixin, '/').'\b/', $docComment) === 1;
    }

    private function shortName(string $className): string
    {
        $position = mb_strrpos($className, '\\');

        return $position === false ? $className : mb_substr($className, $position + 1);
    }
}
