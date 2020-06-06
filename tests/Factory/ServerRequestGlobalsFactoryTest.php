<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Factory;

use Borodulin\Http\Factory\ServerRequestGlobalsFactory;
use Borodulin\Http\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;

class ServerRequestGlobalsFactoryTest extends TestCase
{
    public function testCreateServerRequest(): void
    {
        $factory = new ServerRequestGlobalsFactory();
        $request = $factory->createServerRequest(
            $this->getServer(),
            $this->getCookie(),
            $this->getQuery(),
            $this->getPost(),
            $this->getFiles(),
            (new StreamFactory())->createStream()
        );

        $this->assertEquals('GET', $request->getMethod());
    }

    public function testServerProtocol(): void
    {
        $server = $this->getServer();
        $server['SERVER_PROTOCOL'] = 'HTTP/1.2';
        $factory = new ServerRequestGlobalsFactory();
        $request = $factory->createServerRequest(
            $server,
            $this->getCookie(),
            $this->getQuery(),
            $this->getPost(),
            $this->getFiles(),
            (new StreamFactory())->createStream()
        );

        $this->assertEquals('1.2', $request->getProtocolVersion());
    }

    public function testMethod(): void
    {
        $factory = new ServerRequestGlobalsFactory();
        $server = $this->getServer();
        $server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'POST';
        $server['REQUEST_METHOD'] = 'PUT';

        $request = $factory->createServerRequest(
            $server,
            $this->getCookie(),
            $this->getQuery(),
            $this->getPost(),
            $this->getFiles(),
            (new StreamFactory())->createStream()
        );

        $this->assertEquals('POST', $request->getMethod());

        unset($server['HTTP_X_HTTP_METHOD_OVERRIDE']);

        $request = $factory->createServerRequest(
            $server,
            $this->getCookie(),
            $this->getQuery(),
            $this->getPost(),
            $this->getFiles(),
            (new StreamFactory())->createStream()
        );

        $this->assertEquals('PUT', $request->getMethod());
    }

    public function testHostInfo(): void
    {
        $factory = new ServerRequestGlobalsFactory();
        $server = $this->getServer();

        $server['SERVER_NAME'] = 'yandex.dev';
        $request = $factory->createServerRequest($server, [], [], [], [], (new StreamFactory())->createStream());
        $this->assertEquals('yandex.dev', $request->getUri()->getHost());

        $server['HTTP_HOST'] = 'yandex.host';
        $request = $factory->createServerRequest($server, [], [], [], [], (new StreamFactory())->createStream());
        $this->assertEquals('yandex.host', $request->getUri()->getHost());

        $server['HTTP_X_FORWARDED_HOST'] = 'ya.ru';
        $request = $factory->createServerRequest($server, [], [], [], [], (new StreamFactory())->createStream());
        $this->assertEquals('ya.ru', $request->getUri()->getHost());
    }

    private function getServer(): array
    {
        return [
            'REQUEST_URI' => '/test',
            'SCRIPT_FILENAME' => '/index.php',
            'SCRIPT_NAME' => 'index.php',
        ];
    }

    private function getCookie(): array
    {
        return [];
    }

    private function getQuery(): array
    {
        return [
            'id' => 22,
        ];
    }

    private function getPost(): array
    {
        return [
            'field1' => 'hello',
        ];
    }

    private function getFiles(): array
    {
        return [
            'user_file' => [
                'tmp_name' => 'test2',
                'name' => 'test',
                'type' => 'jpg',
                'size' => '23',
                'error' => UPLOAD_ERR_NO_FILE,
            ],
        ];
    }
}
