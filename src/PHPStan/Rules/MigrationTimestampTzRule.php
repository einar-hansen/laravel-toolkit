<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use Illuminate\Database\Schema\Blueprint;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Timestamp columns in migrations must use the timezone-aware Blueprint
 *
 * @implements Rule<MethodCall>
 */
final readonly class MigrationTimestampTzRule implements Rule
{
    /**
     * Non-timezone-aware Blueprint methods mapped to the variant to use
     * instead. Limited to the three the guideline names.
     *
     * @var array<string, string>
     */
    private const array TZ_REPLACEMENTS = [
        'timestamps' => 'timestampsTz',
        'timestamp' => 'timestampTz',
        'softDeletes' => 'softDeletesTz',
    ];

    /**
     * @param  list<string>  $restrictedPaths  substrings matched against the
     *                                         analysed file's path; the rule
     *                                         only fires for files whose path
     *                                         contains at least one of these.
     */
    public function __construct(private array $restrictedPaths) {}

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

        if (! $this->isWithinRestrictedPath($scope->getFile())) {
            return [];
        }

        if (! $node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->toString();
        $replacement = self::TZ_REPLACEMENTS[$methodName] ?? null;

        if ($replacement === null) {
            return [];
        }

        if (! $this->isBlueprintCall($node, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Migration uses $table->%s(), which creates a `timestamp without time zone` '
                    .'column. Use $table->%s() instead — the untyped variant stores wall-clock '
                    .'time, so the offset is lost and the hour repeated at the end of October is '
                    .'unrecoverable.',
                $methodName,
                $replacement,
            ))
                ->identifier('toolkit.migrations.nonTzTimestamp')
                ->build(),
        ];
    }

    private function isWithinRestrictedPath(string $file): bool
    {
        return array_any($this->restrictedPaths, fn ($restrictedPath): bool => str_contains($file, $restrictedPath));
    }

    private function isBlueprintCall(MethodCall $methodCall, Scope $scope): bool
    {
        return new ObjectType(Blueprint::class)
            ->isSuperTypeOf($scope->getType($methodCall->var))
            ->yes();
    }
}
