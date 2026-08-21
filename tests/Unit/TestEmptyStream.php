<?php

declare(strict_types=1);

namespace Quillstack\Stream\Tests\Unit;

use Quillstack\Stream\EmptyStream;
use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\AssertExceptions;
use Quillstack\UnitTests\Types\AssertBoolean;
use Quillstack\UnitTests\Types\AssertNull;

class TestEmptyStream
{
    private EmptyStream $stream;

    public function __construct(
        private AssertEqual $assertEqual,
        private AssertBoolean $assertBoolean,
        private AssertNull $assertNull,
        private AssertExceptions $assertExceptions
    ) {
        $this->stream = new EmptyStream();
    }

    public function itReadsAsEmpty()
    {
        $this->assertEqual->equal('', (string) $this->stream);
        $this->assertEqual->equal('', $this->stream->read(10));
        $this->assertEqual->equal('', $this->stream->getContents());
        $this->assertEqual->equal(0, $this->stream->getSize());
        $this->assertEqual->equal(0, $this->stream->tell());
        $this->assertBoolean->isTrue($this->stream->eof());
    }

    public function itIsReadableAndNothingElse()
    {
        $this->assertBoolean->isTrue($this->stream->isReadable());
        $this->assertBoolean->isFalse($this->stream->isWritable());
        $this->assertBoolean->isFalse($this->stream->isSeekable());
    }

    public function closingAndRewindingDoNothing()
    {
        $this->stream->close();
        $this->stream->rewind();

        $this->assertNull->isNull($this->stream->detach());
    }

    public function thereIsNoMetadata()
    {
        $this->assertEqual->equal([], $this->stream->getMetadata());
        $this->assertNull->isNull($this->stream->getMetadata('anything'));
    }

    public function itCannotBeSeeked()
    {
        $this->assertExceptions->expect(StreamNotSeekableException::class);

        $this->stream->seek(0);
    }

    public function itCannotBeWrittenTo()
    {
        $this->assertExceptions->expect(StreamNotWritableException::class);

        $this->stream->write('nope');
    }
}
