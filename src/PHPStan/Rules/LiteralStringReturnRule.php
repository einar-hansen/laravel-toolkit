<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * A function declaring `@return literal-string` must actually return one.
 *
 * @implements Rule<Return_>
 */
final class LiteralStringReturnRule implements Rule
{
    public function getNodeType(): string
    {
        return Return_::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->expr instanceof Expr) {
            return [];
        }

        $function = $scope->getFunction();
        if (! $function instanceof PhpFunctionFromParserNodeReflection) {
            return [];
        }

        $variants = $function->getVariants();
        $declared = $variants[0]->getReturnType();

        if (count($variants) !== 1 || ! $declared->isLiteralString()->yes()) {
            return [];
        }

        $returned = $scope->getType($node->expr);
        if ($returned->isLiteralString()->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s() is annotated `@return literal-string` but returns %s. Every byte of a '
                    .'literal-string has to come from source code — build it from string '
                    .'literals, constants, enum cases and other literal-strings, and put '
                    .'anything runtime-derived in a query binding instead.',
                $function->getName(),
                $returned->describe(VerbosityLevel::typeOnly()),
            ))
                ->identifier('toolkit.literalString.return')
                ->build(),
        ];
    }
}
