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
 * An Eloquent model's class docblock carries `@mixin IdeHelperXxx` and nothing
 *
 * @implements Rule<InClassNode>
 */
final class ModelDocblockNoHandwrittenAnnotationsRule implements Rule
{
    private const string MODEL = Model::class;

    /**
     * Tags that duplicate generated output, in the order they are reported.
     * `@property` needs the negative lookahead so it does not also match
     * `@property-read` / `@property-write` and report the same line twice.
     *
     * @var array<string, string>
     */
    private const array GENERATED_TAGS = [
        '@property' => '/@property(?![-\w])/',
        '@property-read' => '/@property-read\b/',
        '@property-write' => '/@property-write\b/',
        '@method static' => '/@method\s+static\b/',
    ];

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

        $docComment = $node->getOriginalNode()->getDocComment()?->getText() ?? '';

        if ($docComment === '') {
            return [];
        }

        $errors = [];

        foreach (self::GENERATED_TAGS as $tag => $pattern) {
            if (preg_match($pattern, $docComment) !== 1) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s hand-writes `%s` on its class docblock. That information belongs in '
                    .'_ide_helper_models.php, which `php artisan ide-helper:models --write-mixin` regenerates from the schema — '
                    ."a hand-written copy has no generator behind it, outranks larastan's "
                    .'reflection, and goes stale on the next migration. Delete the tag and run '
                    .'`php artisan ide-helper:models --write-mixin`.',
                $classReflection->getDisplayName(),
                $tag,
            ))
                ->identifier('toolkit.model.handwrittenDocblockAnnotation')
                ->build();
        }

        foreach ($this->nonIdeHelperMixins($docComment) as $mixin) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s declares `@mixin %s` on its class docblock. Only `@mixin IdeHelper%s` '
                    .'belongs there: any other mixin contributes no column information while '
                    .'still typing $this, which makes magic attribute access look statically '
                    .'valid when nothing has checked it.',
                $classReflection->getDisplayName(),
                $mixin,
                $this->shortName($classReflection->getName()),
            ))
                ->identifier('toolkit.model.handwrittenDocblockAnnotation')
                ->build();
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function nonIdeHelperMixins(string $docComment): array
    {
        if (preg_match_all('/@mixin\s+(\\\\?[\w\\\\]+)/', $docComment, $matches) === 0) {
            return [];
        }

        $offenders = [];

        foreach ($matches[1] as $mixin) {
            if (! str_starts_with($this->shortName($mixin), 'IdeHelper')) {
                $offenders[] = $mixin;
            }
        }

        return $offenders;
    }

    private function shortName(string $className): string
    {
        $position = mb_strrpos($className, '\\');

        return $position === false ? $className : mb_substr($className, $position + 1);
    }
}
