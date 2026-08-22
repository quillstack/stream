<?php

declare(strict_types=1);

namespace Quillstack\Stream\Tests\Unit;

use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;
use Quillstack\Stream\InputStream;
use Quillstack\UnitTests\AssertEmpty;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\AssertExceptions;
use Quillstack\UnitTests\Types\AssertArray;
use Quillstack\UnitTests\Types\AssertBoolean;
use Quillstack\UnitTests\Types\AssertNull;

class TestInputStream
{
    private InputStream $stream;

    public function __construct(
        private AssertEqual $assertEqual,
        private AssertBoolean $assertBoolean,
        private AssertEmpty $assertEmpty,
        private AssertArray $assertArray,
        private AssertNull $assertNull,
        private AssertExceptions $assertExceptions
    ) {
        $this->stream = new InputStream();
    }

    public function emptyInput()
    {
        $stream = new InputStream();

        $this->assertEqual->equal('', $stream->getContents());
        $this->assertEqual->equal('', $stream->read(0));
        $this->assertEqual->equal('', (string) $stream);
        $this->assertEqual->equal(0, $stream->getSize());
    }

    public function emptyGivenInput()
    {
        $contest = null;
        $stream = new InputStream($contest);

        $this->assertEqual->equal('', $stream->getContents());
        $this->assertEqual->equal('', $stream->read(0));
        $this->assertEqual->equal('', (string) $stream);
        $this->assertEqual->equal(0, $stream->getSize());
    }

    public function notEmptyGivenInput()
    {
        $contest = 'test';
        $stream = new InputStream($contest);

        $this->assertEqual->equal('test', $stream->getContents());
        $this->assertEqual->equal('test', $stream->read(0));
        $this->assertEqual->equal('test', (string) $stream);
        $this->assertEqual->equal(4, $stream->getSize());
    }

    /**
     * Closing lets go of what the stream held, the way PSR-7 describes it.
     */
    public function close()
    {
        $this->stream->close();

        $this->assertNull->isNull($this->stream->detach());
    }

    public function tell()
    {
        $tell = $this->stream->tell();

        $this->assertEqual->equal(0, $tell);
    }

    public function eof()
    {
        $eof = $this->stream->eof();

        $this->assertBoolean->isFalse($eof);
    }

    public function isSeekable()
    {
        $isSeekable = $this->stream->isSeekable();

        $this->assertBoolean->isFalse($isSeekable);
    }

    /**
     * PSR-7 has a stream which cannot be seeked say so rather than answer false, which no
     * caller could tell apart from a position of zero.
     */
    public function seek()
    {
        $this->assertExceptions->expect(StreamNotSeekableException::class);

        $this->stream->seek(0);
    }

    public function rewind()
    {
        $this->assertExceptions->expect(StreamNotSeekableException::class);

        $this->stream->rewind();
    }

    public function isWritable()
    {
        $isWritable = $this->stream->isWritable();

        $this->assertBoolean->isFalse($isWritable);
    }

    public function write()
    {
        $this->assertExceptions->expect(StreamNotWritableException::class);

        $this->stream->write('test');
    }

    public function isReadable()
    {
        $isReadable = $this->stream->isReadable();

        $this->assertBoolean->isTrue($isReadable);
    }

    public function getMetadata()
    {
        $metadata = $this->stream->getMetadata();

        $this->assertArray->isArray($metadata);
        $this->assertEmpty->isEmpty($metadata);
    }

    public function getMetadataWithKey()
    {
        $metadata = $this->stream->getMetadata('key');

        $this->assertNull->isNull($metadata);
    }

    /**
     * PSR-7 detaches the resource underneath a stream and hands it over. There is none
     * underneath one holding a string, so there is nothing to hand back, and what it held
     * is gone either way.
     */
    public function detach()
    {
        $this->assertNull->isNull($this->stream->detach());
        $this->assertEqual->equal('', $this->stream->getContents());
        $this->assertEqual->equal('', $this->stream->read(0));
    }
}
