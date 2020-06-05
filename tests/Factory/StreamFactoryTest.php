<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Factory;

use Borodulin\Http\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;

class StreamFactoryTest extends TestCase
{
    public function testCreateStreamFromFile(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile(__FILE__);
        $this->assertNotNull($stream->getSize());
    }

    public function testCreateStreamFromResource(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromResource(fopen('php://memory', 'r+'));
        $stream->write('test');
        $this->assertEquals('test', (string) $stream);
    }

    public function testCreateStream(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('test');
        $this->assertEquals('test', (string) $stream);
    }
}
