<?php

declare(strict_types=1);

namespace Quillstack\Stream;

use Psr\Http\Message\StreamInterface;
use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;

/**
 * A stream holding nothing, for the places PSR-7 promises a stream and there is none. It
 * reads as empty and refuses to be written to, rather than pretending to be either.
 */
final class EmptyStream implements StreamInterface
{
    public function __toString(): string
    {
        return '';
    }

    public function close(): void
    {
        //
    }

    public function detach()
    {
        return null;
    }

    public function getSize(): int
    {
        return 0;
    }

    public function tell(): int
    {
        return 0;
    }

    public function eof(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new StreamNotSeekableException('An empty stream cannot be seeked');
    }

    public function rewind(): void
    {
        //
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new StreamNotWritableException('An empty stream cannot be written to');
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        return '';
    }

    public function getContents(): string
    {
        return '';
    }

    public function getMetadata($key = null)
    {
        return $key === null ? [] : null;
    }
}
