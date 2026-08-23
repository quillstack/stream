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
        $stream = new InputStream('test');

        // `read(0)` asks for nothing and gets nothing; it used to hand back the whole body
        // whatever was asked for, which PSR-7 does not allow.
        $this->assertEqual->equal('', $stream->read(0));
        $this->assertEqual->equal('test', $stream->getContents());
        $this->assertEqual->equal('test', (string) $stream);
        $this->assertEqual->equal(4, $stream->getSize());
    }

    /**
     * Reading takes what was asked for and leaves the rest where it is.
     */
    public function readingTakesWhatWasAskedFor()
    {
        $stream = new InputStream('abcdef');

        $this->assertEqual->equal('abc', $stream->read(3));
        $this->assertEqual->equal(3, $stream->tell());
        $this->assertEqual->equal('de', $stream->read(2));
        $this->assertEqual->equal('f', $stream->getContents());
        $this->assertBoolean->isTrue($stream->eof());
    }

    /**
     * Asking for more than is left gives what is left.
     */
    public function askingForMoreThanIsLeftGivesWhatIsLeft()
    {
        $stream = new InputStream('abc');

        $this->assertEqual->equal('abc', $stream->read(100));
        $this->assertEqual->equal('', $stream->read(100));
    }

    /**
     * `getContents()` is what is left of it; `__toString()` is always the whole thing.
     */
    /**
     * `getContents()` is what is left; casting to a string is all of it, whatever has already
     * been read.
     */
    public function contentsAreWhatIsLeftAndTheStringIsAllOfIt()
    {
        $stream = new InputStream('abcdef');
        $stream->read(3);

        $this->assertEqual->equal('def', $stream->getContents());

        $stream->rewind();
        $stream->read(3);

        $this->assertEqual->equal('abcdef', (string) $stream);
    }

    /**
     * PSR-7 has casting to a string seek to the beginning and read to the end, so afterwards
     * the stream is at the end. This used to hand the body back without moving — convenient,
     * and quietly disagreeing with `FileStream` beside it and with every other implementation
     * of this interface.
     */
    public function castingToAStringLeavesItAtTheEnd()
    {
        $stream = new InputStream('abcdef');
        $stream->read(3);

        $this->assertEqual->equal('abcdef', (string) $stream);
        $this->assertBoolean->isTrue($stream->eof());
        $this->assertEqual->equal(6, $stream->tell());
        $this->assertEqual->equal('', $stream->getContents());

        // And it can be asked again: it seeks first, so it is not spent.
        $this->assertEqual->equal('abcdef', (string) $stream);
    }

    public function itCanBeSeekedAround()
    {
        $stream = new InputStream('abcdef');

        $stream->seek(2);
        $this->assertEqual->equal('cd', $stream->read(2));

        $stream->seek(1, SEEK_CUR);
        $this->assertEqual->equal('f', $stream->read(1));

        $stream->seek(-2, SEEK_END);
        $this->assertEqual->equal('ef', $stream->read(2));

        $stream->rewind();
        $this->assertEqual->equal('abcdef', $stream->getContents());
    }

    public function seekingBeforeTheStartSaysSo()
    {
        $this->assertExceptions->expect(StreamNotSeekableException::class);

        (new InputStream('abc'))->seek(-1);
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

    /**
     * Nothing at all is already at its end, which is what PSR-7 means by it. It used to
     * answer false however far through the reader was.
     */
    public function eof()
    {
        $this->assertBoolean->isTrue($this->stream->eof());
        $this->assertBoolean->isFalse((new InputStream('abc'))->eof());
    }

    /**
     * It is a string held in memory, so of course it is.
     */
    public function isSeekable()
    {
        $this->assertBoolean->isTrue($this->stream->isSeekable());
    }

    /**
     * A stream which has been let go of cannot be moved around any more.
     */
    public function seekingAClosedStreamSaysSo()
    {
        $stream = new InputStream('abc');
        $stream->close();

        $this->assertExceptions->expect(StreamNotSeekableException::class);

        $stream->seek(0);
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
        $this->assertEqual->equal('', $this->stream->read(1));
    }
}
