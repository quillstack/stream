<?php

declare(strict_types=1);

namespace Quillstack\Stream\Tests\Unit;

use Quillstack\Stream\TextStream;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\Types\AssertBoolean;

/**
 * A body on its way out rather than one which arrived.
 */
class TestTextStream
{
    public function __construct(
        private AssertEqual $assertEqual,
        private AssertBoolean $assertBoolean
    ) {
        //
    }

    public function itHoldsWhatItWasGiven()
    {
        $stream = new TextStream('{"hello":"world"}');

        $this->assertEqual->equal('{"hello":"world"}', (string) $stream);
        $this->assertEqual->equal(17, $stream->getSize());
        $this->assertBoolean->isTrue($stream->isReadable());
    }

    public function anEmptyOneIsEmpty()
    {
        $stream = new TextStream();

        $this->assertEqual->equal('', (string) $stream);
        $this->assertEqual->equal(0, $stream->getSize());
    }

    public function itCanBeReadAndRewound()
    {
        $stream = new TextStream('abcdef');

        $this->assertEqual->equal('abc', $stream->read(3));
        $stream->rewind();
        $this->assertEqual->equal('abcdef', $stream->getContents());
    }

    /**
     * It does not read `php://input`, which is the whole reason it is not `InputStream`.
     */
    public function itNeverReachesForTheRequestBody()
    {
        $this->assertEqual->equal('', (string) new TextStream(''));
    }
}
