<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Exceptions;

use LogicException;
use RuntimeException;

/**
 * Fixture for {@see \EinarHansen\Toolkit\PHPStan\Rules\EmptyCatchMustBeExplainedRule}.
 *
 * Only the two bare catches (lines noted in the Pest file) should be flagged.
 * Everything else is a pass case, and the rule test asserts the EXACT error set
 * for this file — so a pass case that wrongly fired shows up as an extra error.
 */
final class EmptyCatches
{
    /** FLAGGED: nothing in the body, nothing said about why. */
    public function bare(): void
    {
        try {
            $this->work();
        } catch (RuntimeException) {
        }
    }

    /** FLAGGED: whitespace is not an explanation either. */
    public function blank(): void
    {
        try {
            $this->work();
        } catch (RuntimeException $runtimeException) {

        }
    }

    /** PASS: a line comment inside the body is the explanation. */
    public function explainedWithLineComment(): void
    {
        try {
            $this->work();
        } catch (RuntimeException) {
            // Concurrent writer got there first; the re-read decides the winner.
        }
    }

    /** PASS: block comments count the same. */
    public function explainedWithBlockComment(): void
    {
        try {
            $this->work();
        } catch (RuntimeException) {
            /* Best-effort telemetry — never worth failing the request over. */
        }
    }

    /** PASS: an explanation placed above the `catch` still explains it. */
    public function explainedAbove(): void
    {
        try {
            $this->work();
        }
        // The lock is advisory; losing it just means someone else is sweeping.
        catch (RuntimeException) {
        }
    }

    /** PASS: a body that does something is not this rule's business. */
    public function handled(): void
    {
        try {
            $this->work();
        } catch (RuntimeException $runtimeException) {
            report($runtimeException);
        }
    }

    /** PASS: multi-catch, both arms handled. */
    public function multiCatch(): void
    {
        try {
            $this->work();
        } catch (RuntimeException|LogicException $e) {
            report($e);
        }
    }

    private function work(): never
    {
        throw new RuntimeException('boom');
    }
}
