<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Files;

use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use PHPUnit\Framework\TestCase;

class FilesTraitTest extends TestCase
{
    use FilesTrait;

    public function testGetFilteredFile(): void
    {
        $this->assertFileExists($this->getFilteredFile());
    }

    public function testGetEmptyFile(): void
    {
        $this->assertFileExists($this->getEmptyFile());
    }

    public function testGetWithHeaderFile(): void
    {
        $this->assertFileExists($this->getWithHeaderFile());
    }

    public function testGetWithoutHeaderFile(): void
    {
        $this->assertFileExists($this->getWithoutHeaderFile());
    }

    public function testExampleFile(): void
    {
        $this->assertFileExists($this->getExampleFile());
    }
}
