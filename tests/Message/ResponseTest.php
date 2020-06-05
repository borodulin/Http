<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Message;

use Borodulin\Http\Factory\StreamFactory;
use Borodulin\Http\Message\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testGetReasonPhrase(): void
    {
        $response = new Response((new StreamFactory())->createStream());
        $this->assertEquals('', $response->getReasonPhrase());
        $response = new Response((new StreamFactory())->createStream(), 201, 'test');
        $this->assertEquals('test', $response->getReasonPhrase());
    }

    public function testGetStatusCode(): void
    {
        $response = new Response((new StreamFactory())->createStream(), 201);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testWithStatus(): void
    {
        $response = new Response((new StreamFactory())->createStream(), 201);
        $this->assertEquals(200, $response->withStatus(200)->getStatusCode());
        $this->assertEquals(201, $response->getStatusCode());
    }
}
