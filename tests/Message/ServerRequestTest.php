<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Message;

use Borodulin\Http\Factory\StreamFactory;
use Borodulin\Http\Factory\UploadedFileFactory;
use Borodulin\Http\Message\ServerRequest;
use Borodulin\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{
    private function createServerRequest(): ServerRequest
    {
        return new ServerRequest(
            new Uri(''),
            (new StreamFactory())->createStream(),
            'POST',
            ['Content-Type' => ['Application/Json']],
            ['REQUEST_METHOD' => 'GET'],
            ['session_id' => '123'],
            ['id' => 12],
            ['file' => (new UploadedFileFactory())
                ->createUploadedFile((new StreamFactory())->createStream()),
            ]
        );
    }

    public function testWithUploadedFiles(): void
    {
        $request = $this->createServerRequest();
        $this->assertEmpty($request->withUploadedFiles([])->getUploadedFiles());
        $this->assertNotEmpty($request->getUploadedFiles());

    }

    public function testWithCookieParams(): void
    {
        $request = $this->createServerRequest();
        $this->assertEmpty($request->withCookieParams([])->getCookieParams());
        $this->assertNotEmpty($request->getCookieParams());
    }

    public function testGetServerParams(): void
    {
        $request = $this->createServerRequest();
        $this->assertNotEmpty($request->getServerParams());
    }

    public function testWithQueryParams(): void
    {
        $request = $this->createServerRequest();
        $this->assertEmpty($request->withQueryParams([])->getQueryParams());
        $this->assertNotEmpty($request->getQueryParams());
    }

    public function testWithAttribute(): void
    {
        $request = $this->createServerRequest();
        $this->assertEquals('test', $request->withAttribute('test', 'test')->getAttribute('test'));
        $this->assertEmpty($request->getAttributes());
    }

    public function testWithParsedBody(): void
    {
        $request = $this->createServerRequest();
        $this->assertNotEmpty($request->withParsedBody(['test' => 'tes'])->getParsedBody());
        $this->assertEmpty($request->getParsedBody());
    }

    public function testWithoutAttribute(): void
    {
        $request = $this->createServerRequest();
        $withAttribute = $request->withAttribute('test', 'test');
        $this->assertEquals('test', $withAttribute->getAttribute('test'));
        $this->assertNull($withAttribute->withoutAttribute('test')->getAttribute('test'));
        $this->assertEmpty($request->getAttributes());
    }
}
