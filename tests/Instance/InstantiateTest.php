<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Instance;

use Inwebo\Csv\Reader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
#[Group('csv')]
#[Group('instantiate')]
class InstantiateTest extends TestCase
{
    public function testInvalidInstantiate(): void
    {
        $this->expectException(\RuntimeException::class);
        new Reader('unknown-file.csv');
    }

    public function testValidWithHeaderInstantiate(): void
    {
        $iterator = new Reader(WITH_HEADER);
        $this->assertInstanceOf(Reader::class, $iterator);
    }

    public function testValidWithoutHeaderInstantiate(): void
    {
        $iterator = (new Reader(WITHOUT_HEADER, hasColName: false));
        $this->assertInstanceOf(Reader::class, $iterator);
    }

    public function testEmpty(): void
    {
        $iterator = (new Reader(EMPTY_FILE, hasColName: false));
        $this->assertInstanceOf(Reader::class, $iterator);

        $i = 0;

        foreach ($iterator->lines() as $item) {
            ++$i;
        }

        $this->assertEquals(0, $i);
    }
}
