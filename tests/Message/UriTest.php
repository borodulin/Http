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
        $uri = new Uri('ssh://test:test@localhost:80/path');
        $this->assertEquals('user1', $uri->withUserInfo('user1')->getUserInfo());
        $this->assertEquals('user1:pass', $uri->withUserInfo('user1', 'pass')->getUserInfo());
        $this->assertEquals('test:test', $uri->getUserInfo());
    }

    public function testWithFragment(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80/path');
        $this->assertEquals('frag', $uri->withFragment('frag')->getFragment());
        $this->assertEquals('', $uri->getFragment());
    }

    public function testWithQuery(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80/path?id=1');
        $this->assertEquals('query', $uri->withQuery('query')->getQuery());
        $this->assertEquals('id=1', $uri->getQuery());
    }

    public function testWithHost(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80/path?id=1');
        $this->assertEquals('ya.ru', $uri->withHost('ya.ru')->getHost());
        $this->assertEquals('localhost', $uri->getHost());
    }

    public function testWithScheme(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80');
        $this->assertEquals('http', $uri->withScheme('http')->getScheme());
        $this->assertEquals('ssh', $uri->getScheme());
    }

    public function testWithPort(): void
    {
        $uri = new Uri('ssh://test:test@localhost:80');
        $this->assertEquals(443, $uri->withPort(443)->getPort());
        $this->assertEquals(80, $uri->getPort());
        $this->assertEquals('ssh://test:test@localhost:22', (string) $uri->withPort(22));
    }
}
