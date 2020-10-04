<?php

declare(strict_types=1);

namespace QuillStack\Http\Stream;

use Psr\Http\Message\StreamInterface;

final class InputStream implements StreamInterface
{
    /**
     * @var string|null
     */
    private ?string $body;

    /**
     * @param null $contest
     */
    public function __construct($contest = null)
    {
        $body = $contest ?? file_get_contents('php://input');
        $this->body = !empty($body) ? $body : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $body = $this->body;
        $this->body = null;

        return $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return strlen($this->body);
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return [];
        }

        return null;
    }
}
