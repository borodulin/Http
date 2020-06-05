<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Factory;

use Borodulin\Http\Factory\UploadedFileFactory;
use Borodulin\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;

class UploadedFileFactoryTest extends TestCase
{
    public function testCreateUploadedFile(): void
    {
        $factory = new UploadedFileFactory();
        $stream = new Stream(fopen('php://memory', 'r+'));
        $uploadedFile = $factory->createUploadedFile($stream);
        $this->assertNull($uploadedFile->getSize());
    }
}
