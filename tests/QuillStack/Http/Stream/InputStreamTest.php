<?php

declare(strict_types=1);

namespace QuillStack\Http\Stream;

use PHPUnit\Framework\TestCase;

final class InputStreamTest extends TestCase
{
    /**
     * @var InputStream
     */
    private InputStream $stream;

    protected function setUp(): void
    {
        $this->stream = new InputStream();
    }

    public function testEmptyInput()
    {
        $stream = new InputStream();

        $this->assertEquals('', $stream->getContents());
        $this->assertEquals('', $stream->read(0));
        $this->assertEquals('', (string) $stream);
        $this->assertEquals(0, $stream->getSize());
    }

    public function testEmptyGivenInput()
    {
        $contest = null;
        $stream = new InputStream($contest);

        $this->assertEquals('', $stream->getContents());
        $this->assertEquals('', $stream->read(0));
        $this->assertEquals('', (string) $stream);
        $this->assertEquals(0, $stream->getSize());
    }

    public function testNotEmptyGivenInput()
    {
        $contest = 'test';
        $stream = new InputStream($contest);

        $this->assertEquals('test', $stream->getContents());
        $this->assertEquals('test', $stream->read(0));
        $this->assertEquals('test', (string) $stream);
        $this->assertEquals(4, $stream->getSize());
    }

    public function testClose()
    {
        $close = $this->stream->close();

        $this->assertFalse($close);
    }

    public function testTell()
    {
        $tell = $this->stream->tell();

        $this->assertEquals(0, $tell);
    }

    public function testEof()
    {
        $eof = $this->stream->eof();

        $this->assertFalse($eof);
    }

    public function testIsSeekable()
    {
        $isSeekable = $this->stream->isSeekable();

        $this->assertFalse($isSeekable);
    }

    public function testSeek()
    {
        $seek = $this->stream->seek(0);

        $this->assertFalse($seek);
    }

    public function testRewind()
    {
        $rewind = $this->stream->rewind();

        $this->assertFalse($rewind);
    }

    public function testIsWritable()
    {
        $isWritable = $this->stream->isWritable();

        $this->assertFalse($isWritable);
    }

    public function testWrite()
    {
        $write = $this->stream->write('test');

        $this->assertFalse($write);
    }

    public function testIsReadable()
    {
        $isReadable = $this->stream->isReadable();

        $this->assertTrue($isReadable);
    }

    public function testGetMetadata()
    {
        $metadata = $this->stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertEmpty($metadata);
    }

    public function testGetMetadataWithKey()
    {
        $metadata = $this->stream->getMetadata('key');

        $this->assertNull($metadata);
    }

    public function testDetach()
    {
        $body = $this->stream->detach();

        $this->assertEquals('', $body);
        $this->assertEquals('', $this->stream->getContents());
        $this->assertEquals('', $this->stream->read(0));
    }
}
