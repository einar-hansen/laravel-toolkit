<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

/**
 * Inherits the tag from its parent — repeating it would be noise, and a repeat
 * that drifted would be worse than none.
 */
final class InheritsAnnotationResource extends AnnotatedResource {}
