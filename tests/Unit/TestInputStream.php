<?php

declare(strict_types=1);

namespace Quillstack\Stream\Tests\Unit;

use Quillstack\Stream\InputStream;
use Quillstack\UnitTests\AssertEmpty;
use Quillstack\UnitTests\AssertEqual;
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
        private AssertNull $assertNull
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

    public function close()
    {
        $close = $this->stream->close();

        $this->assertBoolean->isFalse($close);
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

    public function seek()
    {
        $seek = $this->stream->seek(0);

        $this->assertBoolean->isFalse($seek);
    }

    public function rewind()
    {
        $rewind = $this->stream->rewind();

        $this->assertBoolean->isFalse($rewind);
    }

    public function isWritable()
    {
        $isWritable = $this->stream->isWritable();

        $this->assertBoolean->isFalse($isWritable);
    }

    public function write()
    {
        $write = $this->stream->write('test');

        $this->assertBoolean->isFalse($write);
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

    public function detach()
    {
        $body = $this->stream->detach();

        $this->assertEqual->equal('', $body);
        $this->assertEqual->equal('', $this->stream->getContents());
        $this->assertEqual->equal('', $this->stream->read(0));
    }
}
