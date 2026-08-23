<?php

declare(strict_types=1);

namespace Quillstack\Stream;

/**
 * A stream over a string somebody already has.
 *
 * `InputStream` will do this too, but it is named for where a request arrives from, and a
 * body being sent somewhere is not that. Same behaviour, honest name.
 */
final class TextStream extends InputStream
{
    public function __construct(string $content = '')
    {
        parent::__construct($content);
    }
}
