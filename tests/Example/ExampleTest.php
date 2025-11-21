<?php

namespace Example;

use Inwebo\Csv\Reader;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
class ExampleTest extends TestCase
{
    use FilesTrait;

    public function testExample(): void
    {
        $reader = new Reader($this->getExampleFile(), hasHeader: true);

        /**
         * A row MUST have a Gender value, default U.
         */
        $reader->addSanitizer(function (array $row) {
            if (is_null($row['Gender'])) {
                $row['Gender'] = 'U';
            }
        })
        /**
         * A row MUST have Salary default 0.
         */
        ->addSanitizer(function (array &$row) {
            if (is_null($row['Salary'])) {
                $row['Salary'] = 0;
            }
        })
        /**
         * A row MUST have Salary default 0.
         */
        ->addSanitizer(function (array &$row) {
            if (is_null($row['Salary'])) {
                $row['Salary'] = 0;
            }

            if (is_string($row['Salary'])) {
                $row['Salary'] = (int) $row['Salary'];
            }
        })
        ->addSanitizer(function (array &$row) {
            if (is_null($row['City'])) {
                $row['City'] = 'Toulouse';
            }
        })
        ->addSanitizer(function (array &$row) {
            if (is_null($row['Age'])) {
                $row['Age'] = 1;
            } else {
                $row['Age'] = (int) $row['Age'];
            }
        })
        ->addSanitizer(function (array &$row) {
            $row['Name'] = trim($row['Name']);
        })
        ->addSanitizer(function (array &$row) {
            $gender = strtolower($row['Gender']);

            if (str_starts_with($gender, 'm')) {
                $row['Gender'] = 'M';
            }

            if (str_starts_with($gender, 'f')) {
                $row['Gender'] = 'F';
            }
        })
        ;

        foreach ($reader->lines() as $row) {
            $this->assertNotNull($row['City']);
            $this->assertNotNull($row['Salary']);
            $this->assertNotNull($row['Age']);
            $this->assertIsInt($row['Salary']);
            $this->assertIsInt($row['Age']);
            $this->assertNotNull($row['Gender']);
            $this->assertFalse(str_starts_with($row['Name'], ' '));
        }
    }
}
