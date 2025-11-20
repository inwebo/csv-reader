<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Filtered;

use Inwebo\Csv\Reader;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
class FilteredTest extends TestCase
{
    use FilesTrait;

    public function testFilteredGenderM(): void
    {
        $reader = new Reader($this->getFilteredFile());

        $reader->addFilter(function (
            /*
             * @var array{
             *     Name: string,
             *     Age: int,
             *     City: string,
             *     Salary: string,
             *     Gender: string,
             * } $line
             */
            $line,
        ) {
            return 'M' === $line['Gender'];
        });

        /** @var \Generator<array<string, string>> $generator */
        $generator = $reader->lines();
        foreach ($generator as $line) {
            $this->assertEquals('M', $line['Gender']);
        }
    }
}
