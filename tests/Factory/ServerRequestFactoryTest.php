<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Factory;

use Borodulin\Http\Factory\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class ServerRequestFactoryTest extends TestCase
{
    public function testCreateServerRequest(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', 'http://localhost');
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('localhost', $request->getUri()->getHost());
    }

    public function testInvalidUri(): void
    {
        $factory = new ServerRequestFactory();
        $uri = new class() {
        };
        $this->expectException(\InvalidArgumentException::class);
        $factory->createServerRequest('POST', $uri);
    }
}
