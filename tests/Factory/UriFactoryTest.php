<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Factory;

use Borodulin\Http\Factory\UriFactory;
use PHPUnit\Framework\TestCase;

class UriFactoryTest extends TestCase
{
    public function testCreateUri(): void
    {
        $factory = new UriFactory();
        $uri = $factory->createUri('http://localhost/path?id=1');
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('/path', $uri->getPath());
        $this->assertEquals('id=1', $uri->getQuery());
    }
}
