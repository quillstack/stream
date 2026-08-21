<?php

declare(strict_types=1);

namespace Quillstack\Stream;

use Psr\Http\Message\StreamInterface;
use Quillstack\Stream\Exceptions\StreamException;
use Quillstack\Stream\Exceptions\StreamNotReadableException;
use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;

/**
 * A stream over a file, opened when it is first read rather than when it is built, so a file
 * nobody reads is never opened.
 */
class FileStream implements StreamInterface
{
    /**
     * @var resource|null
     */
    private $handle;

    private bool $detached = false;

    public function __construct(
        private readonly string $path,
        private readonly string $mode = 'rb'
    ) {
        //
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (StreamException) {
            return '';
        }
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
        }

        $this->handle = null;
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $handle = $this->handle;
        $this->handle = null;
        $this->detached = true;

        return $handle;
    }

    public function getSize(): ?int
    {
        $size = @filesize($this->path);

        return $size === false ? null : $size;
    }

    public function tell(): int
    {
        $position = ftell($this->open());

        if ($position === false) {
            throw new StreamNotSeekableException("Unable to tell the position in: {$this->path}");
        }

        return $position;
    }

    public function eof(): bool
    {
        return feof($this->open());
    }

    public function isSeekable(): bool
    {
        return !$this->detached;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (fseek($this->open(), $offset, $whence) !== 0) {
            throw new StreamNotSeekableException("Unable to seek in: {$this->path}");
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return !$this->detached && !str_starts_with($this->mode, 'r') || str_contains($this->mode, '+');
    }

    public function write($string): int
    {
        if (!$this->isWritable()) {
            throw new StreamNotWritableException("Not open for writing: {$this->path}");
        }

        $written = fwrite($this->open(), $string);

        if ($written === false) {
            throw new StreamNotWritableException("Unable to write to: {$this->path}");
        }

        return $written;
    }

    public function isReadable(): bool
    {
        return !$this->detached && (str_starts_with($this->mode, 'r') || str_contains($this->mode, '+'));
    }

    public function read($length): string
    {
        if (!$this->isReadable()) {
            throw new StreamNotReadableException("Not open for reading: {$this->path}");
        }

        $read = fread($this->open(), max(1, $length));

        return $read === false ? '' : $read;
    }

    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new StreamNotReadableException("Not open for reading: {$this->path}");
        }

        $contents = stream_get_contents($this->open());

        return $contents === false ? '' : $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        if ($this->detached) {
            return $key === null ? [] : null;
        }

        $metadata = stream_get_meta_data($this->open());

        return $key === null ? $metadata : ($metadata[$key] ?? null);
    }

    /**
     * @return resource
     */
    private function open()
    {
        if ($this->detached) {
            throw new StreamNotReadableException("The stream over {$this->path} was detached");
        }

        if ($this->handle === null) {
            $handle = @fopen($this->path, $this->mode);

            if ($handle === false) {
                throw new StreamNotReadableException("Unable to open: {$this->path}");
            }

            $this->handle = $handle;
        }

        return $this->handle;
    }
}
