<?php

declare(strict_types=1);

namespace QuillStack\Http\Stream;

use PHPUnit\Framework\TestCase;

final class InputStreamTest extends TestCase
{
    public function testEmptyInput()
    {
        $stream = new InputStream();

        $this->assertNull($stream->getContents());
    }

    public function testEmptyGivenInput()
    {
        $contest = null;
        $stream = new InputStream();

        $this->assertNull($stream->getContents());
    }
}
