<?php

declare(strict_types=1);

namespace Quillstack\Stream\Tests\Unit;

use Quillstack\Stream\Exceptions\StreamNotReadableException;
use Quillstack\Stream\Exceptions\StreamNotSeekableException;
use Quillstack\Stream\Exceptions\StreamNotWritableException;
use Quillstack\Stream\FileStream;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\AssertExceptions;
use Quillstack\UnitTests\Types\AssertBoolean;
use Quillstack\UnitTests\Types\AssertNull;

class TestFileStream
{
    private string $path;

    public function __construct(
        private AssertEqual $assertEqual,
        private AssertBoolean $assertBoolean,
        private AssertNull $assertNull,
        private AssertExceptions $assertExceptions
    ) {
        $this->path = sys_get_temp_dir() . '/quillstack-stream-' . getmypid() . '.txt';
        file_put_contents($this->path, 'Quillstack');
    }

    public function itReadsTheFile()
    {
        $stream = new FileStream($this->path);

        $this->assertEqual->equal('Quillstack', (string) $stream);
        $this->assertEqual->equal(10, $stream->getSize());
    }

    public function itReadsInPieces()
    {
        $stream = new FileStream($this->path);

        $this->assertEqual->equal('Quill', $stream->read(5));
        $this->assertEqual->equal(5, $stream->tell());
        $this->assertEqual->equal('stack', $stream->getContents());
        $this->assertBoolean->isTrue($stream->eof());

        $stream->rewind();
        $this->assertEqual->equal(0, $stream->tell());
    }

    public function itSeeks()
    {
        $stream = new FileStream($this->path);
        $stream->seek(5);

        $this->assertEqual->equal('stack', $stream->getContents());
        $this->assertBoolean->isTrue($stream->isSeekable());
    }

    public function readingAndWritingFollowTheMode()
    {
        $reading = new FileStream($this->path);
        $writing = new FileStream($this->path, 'r+b');

        $this->assertBoolean->isTrue($reading->isReadable());
        $this->assertBoolean->isFalse($reading->isWritable());
        $this->assertBoolean->isTrue($writing->isWritable());
    }

    public function whatIsWrittenIsThere()
    {
        $path = $this->path . '.written';
        $stream = new FileStream($path, 'w+b');

        $this->assertEqual->equal(5, $stream->write('hello'));
        $stream->rewind();
        $this->assertEqual->equal('hello', $stream->getContents());

        $stream->close();
        unlink($path);
    }

    public function writingToSomethingOpenedForReadingIsRefused()
    {
        $this->assertExceptions->expect(StreamNotWritableException::class);

        (new FileStream($this->path))->write('nope');
    }

    public function readingFromSomethingOpenedForWritingIsRefused()
    {
        $path = $this->path . '.write-only';
        $stream = new FileStream($path, 'wb');

        try {
            $this->assertExceptions->expect(StreamNotReadableException::class);
            $stream->read(1);
        } finally {
            $stream->close();
            @unlink($path);
        }
    }

    public function seekingBeforeTheStartIsRefused()
    {
        $this->assertExceptions->expect(StreamNotSeekableException::class);

        (new FileStream($this->path))->seek(-10);
    }

    public function afileWhichIsNotThereIsReported()
    {
        $this->assertExceptions->expect(StreamNotReadableException::class);

        (new FileStream('/quillstack/not/here'))->getContents();
    }

    /**
     * A file nobody reads is never opened, so building the stream over one which is not
     * there says nothing until something asks for it.
     */
    public function afileIsOpenedOnlyWhenItIsRead()
    {
        $stream = new FileStream('/quillstack/not/here');

        $this->assertNull->isNull($stream->getSize());
        $this->assertEqual->equal('', (string) $stream);
    }

    public function metadataComesFromTheStream()
    {
        $stream = new FileStream($this->path);

        $this->assertEqual->equal($this->path, $stream->getMetadata('uri'));
        $this->assertBoolean->isTrue(is_array($stream->getMetadata()));
    }

    public function detachingHandsTheHandleOver()
    {
        $stream = new FileStream($this->path);
        $stream->read(1);

        $this->assertBoolean->isTrue(is_resource($stream->detach()));
        $this->assertBoolean->isFalse($stream->isReadable());
        $this->assertEqual->equal([], $stream->getMetadata());
        $this->assertNull->isNull($stream->getMetadata('uri'));
    }

    public function readingAfterDetachingIsRefused()
    {
        $stream = new FileStream($this->path);
        $stream->detach();

        $this->assertExceptions->expect(StreamNotReadableException::class);
        $stream->getContents();
    }
}
