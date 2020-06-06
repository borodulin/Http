<?php

declare(strict_types=1);

namespace Borodulin\Http\Tests\Message;

use Borodulin\Http\Factory\StreamFactory;
use Borodulin\Http\Factory\UploadedFileFactory;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
    public function testUploadedFile(): void
    {
        $uploadedFile = (new UploadedFileFactory())
            ->createUploadedFile((new StreamFactory())->createStream());
        $this->assertNull($uploadedFile->getSize());
        $this->assertNull($uploadedFile->getClientFilename());
        $this->assertNull($uploadedFile->getClientMediaType());
        $this->assertEquals(0, $uploadedFile->getError());
    }
}
