<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Message;

use Borodulin\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function testGetAuthority(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80/path');
        $this->assertEquals('test:test@localhost', $uri->getAuthority());
    }

    public function testGetHost(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80/path');
        $this->assertEquals('localhost', $uri->getHost());
    }

    public function testWithPath(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80/path');
        $this->assertEquals('/testPath', $uri->withPath('/testPath')->getPath());
        $this->assertEquals('/path', $uri->getPath());
    }

    public function testWithUserInfo(): void
    {
    }

    public function testGetPath(): void
    {
    }

    public function testWithFragment(): void
    {
    }

    public function testGetPort(): void
    {
    }

    public function testGetQuery(): void
    {
    }

    public function testWithQuery(): void
    {
    }

    public function testGetFragment(): void
    {
    }

    public function testGetScheme(): void
    {
    }

    public function testWithHost(): void
    {
    }

    public function testGetUserInfo(): void
    {
    }

    public function testWithScheme(): void
    {
    }

    public function testWithPort(): void
    {
    }
}
