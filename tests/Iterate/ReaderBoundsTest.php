<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Iterate;

use Inwebo\Csv\Reader;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\HasReaderTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
#[Group('csv')]
#[Group('with-header')]
class ReaderBoundsTest extends TestCase
{
    use FilesTrait;
    use HasReaderTrait;
    private ?Reader $reader;

    public function setUp(): void
    {
        $this->reader = new Reader($this->getWithHeaderFile(), hasHeader: true);
        $this->assertTrue($this->getReader()->hasHeader());
    }

    public function tearDown(): void
    {
        $this->reader = null;
    }

    public function testBoundsWWithHeader(): void
    {
        /**
         * @var array<int, array<string, string>> $lines
         */
        $lines = iterator_to_array($this->getReader()->lines(1, 2));
        $this->assertEquals('Charles', $lines[0]['Firstname']);
        $this->assertEquals('Georges', $lines[1]['Firstname']);
    }

    public function testBoundsWWithoutHeader(): void
    {
        $reader = new Reader($this->getWithHeaderFile(), hasHeader: true);
        /**
         * @var array<int, array<string, string>> $lines
         */
        $lines = iterator_to_array($reader->lines(1, 2));
        $this->assertEquals('Charles', $lines[0]['Firstname']);
        $this->assertEquals('Georges', $lines[1]['Firstname']);
    }
}
