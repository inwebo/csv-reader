<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Fixtures\Model;

use Inwebo\Csv\Reader;

trait HasReaderTrait
{
    public function getReader(): Reader
    {
        return $this->reader; /* @phpstan-ignore-line */
    }
}
