<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Stream;

use Borodulin\Http\Factory\StreamFactory;
use Borodulin\Http\Stream\InvalidStreamException;
use Borodulin\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testIsWritable(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $this->assertTrue($stream->isWritable());
    }

    public function testWrite(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $stream->write(' Test');
        $this->assertEquals('Sample Test', (string) $stream);
    }

    public function testGetMetadata(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $stream->write(' Test');
        $this->assertNull($stream->getMetadata('size'));
        $this->assertIsArray($stream->getMetadata());
    }

    public function testRead(): void
    {
        $stream = (new StreamFactory())->createStream('Sample Test');
        $stream->rewind();
        $this->assertEquals('Sample', $stream->read(6));
    }

    public function testEof(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $stream->read(1000);
        $this->assertTrue($stream->eof());
        $stream->rewind();
        $this->assertFalse($stream->eof());
    }

    public function testTell(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $stream->write(' Test');
        $stream->seek(7);
        $this->assertEquals(7, $stream->tell());
        $stream->close();
        $this->expectException(InvalidStreamException::class);
        $stream->tell();
    }

    public function testClose(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $stream->close();
        $this->assertEquals('', (string) $stream);
        $stream->close();
        $this->expectException(InvalidStreamException::class);
        $stream->getContents();
    }

    public function testIsReadable(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $this->assertTrue($stream->isReadable());
    }

    public function testGetContents(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $stream->write(' Test');
        $stream->seek(7);
        $this->assertEquals('Test', $stream->getContents());
        $stream->rewind();
        $this->assertEquals('Sample Test', $stream->read(1000));
    }

    public function testIsSeekable(): void
    {
        $stream = (new StreamFactory())->createStream('Sample');
        $this->assertTrue($stream->isSeekable());
    }

    public function testDetach(): void
    {
        $stream = (new StreamFactory())->createStream('Sample Test');
        $stream->detach();
        $this->assertEquals('', (string) $stream);
        $stream->close();
        $this->expectException(InvalidStreamException::class);
        $stream->getContents();
    }

    public function testGetSize(): void
    {
        $stream = (new StreamFactory())->createStream('Sample Test');
        $this->assertEquals(11, $stream->getSize());
    }

    public function testInvalidHandle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Stream(null);
    }

    public function testWriteToReadonly(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile('php://memory');
        $this->expectException(InvalidStreamException::class);
        $stream->write('test');
    }

    public function testWriteError(): void
    {
        $stream = (new StreamFactory())->createStream('test');
        $stream->close();
        $this->expectException(InvalidStreamException::class);
        $stream->write('test');
    }
}
