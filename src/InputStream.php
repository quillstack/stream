<?php

declare(strict_types=1);

namespace Quillstack\Stream;

use Psr\Http\Message\StreamInterface;

class InputStream implements StreamInterface
{
    private ?string $body;

    public function __construct($contest = null)
    {
        $body = $contest ?? file_get_contents('php://input');
        $this->body = !empty($body) ? $body : '';
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $body = $this->body;
        $this->body = null;

        return $body;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        return strlen($this->body);
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
    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        return false;
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
    public function write($string)
    {
        return false;
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
