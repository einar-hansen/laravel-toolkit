<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A catch block that discards the exception must say why.
 *
 * @implements Rule<Catch_>
 */
final readonly class EmptyCatchMustBeExplainedRule implements Rule
{
    /**
     * @param  list<string>  $excludedPaths  substrings matched against the
     *                                       analysed file's path; a file whose
     *                                       path contains any of them is skipped.
     */
    public function __construct(private array $excludedPaths = []) {}

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Catch_) {
            return [];
        }

        if ($this->isExcluded($scope->getFile())) {
            return [];
        }

        // Any statement at all means the failure is being handled — logged,
        // rethrown, converted, defaulted. Only a body with nothing in it is
        // the shape this rule is about.
        if ($node->stmts !== []) {
            return [];
        }

        if ($this->hasExplanation($node)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Empty catch block with no explanation. A discarded exception is '
                    .'indistinguishable from a swallowed failure — add a comment saying '
                    .'why this one is safe to ignore, or handle it (log, rethrow, or '
                    .'surface it to the caller).',
            )
                ->identifier('toolkit.exceptions.unexplainedEmptyCatch')
                ->line($node->getStartLine())
                ->build(),
        ];
    }

    /**
     * A comment inside an empty `catch {}` is attached by PhpParser as an
     * end-of-block comment on the Catch_ node itself, because there is no
     * statement for it to lead. Leading comments (above the `catch`, or above
     * the `try` when the two are read together) count as well — the explanation
     * is what matters, not where the author put it.
     */
    private function hasExplanation(Catch_ $catch): bool
    {
        if ($catch->getAttribute('endComments') !== null) {
            return true;
        }

        return $catch->getComments() !== [];
    }

    private function isExcluded(string $file): bool
    {
        return array_any($this->excludedPaths, fn (string $excludedPath): bool => str_contains($file, $excludedPath));
    }
}
