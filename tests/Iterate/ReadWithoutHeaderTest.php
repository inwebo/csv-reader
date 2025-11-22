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
#[Group('without-header')]
class ReadWithoutHeaderTest extends TestCase
{
    use FilesTrait;
    use HasReaderTrait;

    private ?Reader $reader;

    public function setUp(): void
    {
        $this->reader = new Reader($this->getWithoutHeaderFile(), hasHeaders: false);
        $this->assertFalse($this->getReader()->hasHeaders());
    }

    public function tearDown(): void
    {
        $this->reader = null;
    }

    public function testLineAt(): void
    {
        $headers = $this->getReader()->rowAt(1);

        $this->assertIsArray($headers);
        $this->assertEquals(2, $headers[0]);
        $this->assertEquals('Georges', $headers[1]);
        $this->assertEquals('Pompidou', $headers[2]);
        $this->assertEquals('', $headers[3]);
    }

    public function testMapping(): void
    {
        $this->getReader()
            ->setHeader(0, 'Id')
            ->setHeader(1, 'Firstname')
            ->setHeader(2, 'Lastname')
            ->setHeader(3, 'Email')
        ;

        $line = $this->getReader()->rowAt(0);

        $this->assertIsArray($line);

        $this->assertEquals(1, $line['Id']);
        $this->assertEquals('Charles', $line['Firstname']);
        $this->assertEquals('de Gaulle', $line['Lastname']);
        $this->assertEquals('', $line['Email']);
    }
}
