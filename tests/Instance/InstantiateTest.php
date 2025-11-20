<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Instance;

use Inwebo\Csv\Reader;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
#[Group('csv')]
#[Group('instantiate')]
class InstantiateTest extends TestCase
{
    use FilesTrait;

    public function testInvalidInstantiate(): void
    {
        $this->expectException(\RuntimeException::class);
        new Reader('unknown-file.csv');
    }

    public function testValidWithHeaderInstantiate(): void
    {
        $reader = new Reader($this->getWithHeaderFile());
        $this->assertInstanceOf(Reader::class, $reader);
    }

    public function testValidWithoutHeaderInstantiate(): void
    {
        $iterator = (new Reader($this->getWithoutHeaderFile(), hasHeader: false));
        $this->assertInstanceOf(Reader::class, $iterator);
    }

    public function testEmpty(): void
    {
        $reader = (new Reader($this->getEmptyFile(), hasHeader: false));
        $this->assertInstanceOf(Reader::class, $reader);

        $lines = iterator_to_array($reader->lines());

        $this->assertCount(0, $lines);
    }
}
