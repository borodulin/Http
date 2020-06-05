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

        $this->assertEquals('PUT', $request->getMethod());
    }

    private function getServer(): array
    {
        return [
            'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT',
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
