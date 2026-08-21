<?php

declare(strict_types=1);

namespace Quillstack\Stream;

use Psr\Http\Message\StreamInterface;
use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;

class InputStream implements StreamInterface
{
    private ?string $body;

    public function __construct(?string $contest = null)
    {
        $body = $contest ?? file_get_contents('php://input');
        $this->body = !empty($body) ? $body : '';
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->body ?? '';
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
    public function getSize()
    {
        return strlen($this->body ?? '');
    }

    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new StreamNotSeekableException('This stream cannot be seeked');
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        throw new StreamNotSeekableException('This stream cannot be rewound');
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable()
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
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        return $this->body ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        return $this->body ?? '';
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
