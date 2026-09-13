<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags `response()->json(['message' => '<string literal>'], ...)` in
 *
 * @implements Rule<MethodCall>
 */
final readonly class NoHardcodedApiMessageRule implements Rule
{
    /**
     * Status sentinels that are NEVER user-facing copy — webhook handlers
     * returning `'message' => 'OK'` to Twilio/Stripe/Resend etc.
     *
     * @var list<string>
     */
    private const array MACHINE_SENTINELS = ['OK', 'PONG', 'ACK'];

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

        if (! $this->isResponseJsonCall($node)) {
            return [];
        }

        if ($node->isFirstClassCallable()) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $bodyArg = $args[0]->value;
        if (! $bodyArg instanceof Array_) {
            return [];
        }

        $errors = [];

        foreach ($bodyArg->items as $item) {
            if (! $item instanceof ArrayItem) {
                continue;
            }

            if (! $item->key instanceof String_) {
                continue;
            }

            if ($item->key->value !== 'message') {
                continue;
            }

            if (! $item->value instanceof String_) {
                // Already wrapped in __(), Lang::get(), or a variable — pass.
                continue;
            }

            $literal = $item->value->value;

            if (mb_trim($literal) === '') {
                continue;
            }

            if (in_array($literal, self::MACHINE_SENTINELS, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Hardcoded API message "%s" in response()->json(). '
                    .'Translate user-facing messages with __() or trans(), '
                    ."or 'message' => __('messages.namespace.key') for success responses.",
                $literal,
            ))
                ->identifier('toolkit.i18n.hardcodedApiMessage')
                ->line($item->value->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function isWithinRestrictedPath(string $file): bool
    {
        return array_any($this->restrictedPaths, fn (string $restrictedPath): bool => str_contains($file, $restrictedPath));
    }

    /**
     * Match `response()->json(...)` exactly. Other `->json()` callers (HTTP
     * client responses, etc.) are not user-facing API responses.
     */
    private function isResponseJsonCall(MethodCall $methodCall): bool
    {
        if (! $methodCall->name instanceof Identifier) {
            return false;
        }

        if ($methodCall->name->toString() !== 'json') {
            return false;
        }

        if (! $methodCall->var instanceof FuncCall) {
            return false;
        }

        if (! $methodCall->var->name instanceof Name) {
            return false;
        }

        return $methodCall->var->name->toString() === 'response';
    }
}
