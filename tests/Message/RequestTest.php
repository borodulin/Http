<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Message;

use Borodulin\Http\Factory\StreamFactory;
use Borodulin\Http\Message\Request;
use Borodulin\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testGetUri(): void
    {
        $request = new Request(new Uri('http://localhost'), (new StreamFactory())->createStream());
        $this->assertEquals('localhost', $request->getUri()->getHost());
    }

    public function testGetMethod(): void
    {
        $request = new Request(new Uri('http://localhost'), (new StreamFactory())->createStream());
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testWithRequestTarget(): void
    {
        $request = new Request(new Uri('http://localhost'), (new StreamFactory())->createStream());
        $this->assertEquals('/', $request->getRequestTarget());
        $this->assertEquals('/test', $request->withRequestTarget('/test')->getRequestTarget());
        $this->assertEquals('/', $request->getRequestTarget());
    }

    public function testWithMethod(): void
    {
        $request = new Request(new Uri('http://localhost'), (new StreamFactory())->createStream());
        $this->assertEquals('POST', $request->withMethod('POST')->getMethod());
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testWithUri(): void
    {
        $request = new Request(new Uri('http://localhost'), (new StreamFactory())->createStream());
        $this->assertEquals('rambler.ru', $request->withUri(new Uri('http://rambler.ru'))->getUri()->getHost());
        $this->assertEquals('localhost', $request->getUri()->getHost());
    }
}
