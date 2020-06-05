<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Message;

use Borodulin\Http\Factory\StreamFactory;
use Borodulin\Http\Message\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testWithProtocolVersion(): void
    {
        $message = new Message((new StreamFactory())->createStream('test'));
        $this->assertEquals('1.1', $message->getProtocolVersion());
        $this->assertEquals('test', (string) $message->getBody());
        $this->assertEquals('1.2', $message->withProtocolVersion('1.2')->getProtocolVersion());
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

    public function testGetHeader(): void
    {
        $message = new Message((new StreamFactory())->createStream('test'), ['test' => []]);
        $this->assertEquals([], $message->getHeader('test'));
    }

    public function testHasHeader(): void
    {
        $message = new Message((new StreamFactory())->createStream('test'), ['test' => []]);
        $this->assertTrue($message->hasHeader('test'));
        $this->assertFalse($message->hasHeader('test1'));
    }

    public function testWithBody(): void
    {
        $message = new Message((new StreamFactory())->createStream('test'));
        $this->assertEquals('test', (string) $message->getBody());
        $withBody = $message->withBody((new StreamFactory())->createStream('new test'));
        $this->assertEquals('new test', (string) $withBody->getBody());
        $this->assertEquals('test', (string) $message->getBody());
    }

    public function testWithoutHeader(): void
    {
        $message = new Message((new StreamFactory())->createStream('test'));

    }

    public function testGetHeaderLine(): void
    {
    }

    public function testWithHeader(): void
    {
    }

    public function testWithAddedHeader(): void
    {
    }

    public function testGetProtocolVersion(): void
    {
    }

    public function testGetHeaders(): void
    {
    }

    public function testGetBody(): void
    {
    }
}
