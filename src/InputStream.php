<?php

declare(strict_types=1);

namespace Quillstack\Stream;

use Psr\Http\Message\StreamInterface;
use Quillstack\Stream\Exceptions\StreamNotReadableException;
use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;

class InputStream implements StreamInterface
{
    private ?string $body;

    /**
     * How far through it a reader has got.
     *
     * There used to be no such thing: `tell()` answered zero for ever, `eof()` answered false
     * for ever, and `read($length)` handed back the whole body however few bytes were asked
     * for — which PSR-7 does not allow and which nothing reading in chunks could survive.
     */
    private int $position = 0;

    /**
     * @param ?string $content what the stream holds; without it, whatever was sent to this
     *                         process. The parameter was called `contest` for years.
     */
    public function __construct(?string $content = null)
    {
        $body = $content ?? file_get_contents('php://input');
        $this->body = !empty($body) ? $body : '';
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        // PSR-7 has this seek to the beginning and read to the end, so afterwards the stream is
        // at the end and `eof()` says so. Handing back the body without moving was convenient
        // and quietly disagreed with `FileStream` beside it, and with every other
        // implementation of this interface.
        $this->rewind();

        return $this->getContents();
    }

    /**
     * {@inheritDoc}
     *
     * PSR-7 closes a stream and whatever it sits on. There is nothing underneath this one
     * but the string it holds, so closing lets go of it. It used to return false, which
     * neither the interface nor a caller has any use for.
     */
    public function close(): void
    {
        $this->body = null;
        $this->position = 0;
    }

    /**
     * {@inheritDoc}
     */
    /**
     * {@inheritDoc}
     *
     * There is no resource underneath a stream holding a string, so there is none to hand
     * back. What it held is gone either way.
     */
    public function detach()
    {
        $this->body = null;

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        return strlen($this->body ?? '');
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function eof(): bool
    {
        return $this->position >= strlen($this->body ?? '');
    }

    /**
     * {@inheritDoc}
     */
    /**
     * {@inheritDoc}
     *
     * It is a string held in memory, so of course it is.
     */
    public function isSeekable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($this->body === null) {
            throw new StreamNotSeekableException('This stream has been closed');
        }

        $size = strlen($this->body);

        $position = match ($whence) {
            SEEK_CUR => $this->position + $offset,
            SEEK_END => $size + $offset,
            default => $offset,
        };

        if ($position < 0) {
            throw new StreamNotSeekableException("Cannot seek to {$position}");
        }

        $this->position = $position;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function write($string): int
    {
        throw new StreamNotWritableException('This stream cannot be written to');
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($length): string
    {
        if ($length < 0) {
            throw new StreamNotReadableException('Cannot read a negative number of bytes');
        }

        $read = substr($this->body ?? '', $this->position, $length);
        $this->position += strlen($read);

        return $read;
    }

    /**
     * {@inheritDoc}
     */
    /**
     * {@inheritDoc}
     *
     * What is left of it, which is what PSR-7 means: everything from where the reader is to
     * the end. `__toString()` is the one that always gives back the whole thing.
     */
    public function getContents(): string
    {
        $rest = substr($this->body ?? '', $this->position);
        $this->position = strlen($this->body ?? '');

        return $rest;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return [];
        }

        return null;
    }
}
