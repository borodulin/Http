<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Factory;

use Borodulin\Http\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(201, $response->withStatus(201)->getStatusCode());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
