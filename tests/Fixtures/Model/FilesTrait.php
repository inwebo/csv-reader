<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Fixtures\Model;

trait FilesTrait
{
    public function getFilteredFile(): string
    {
        return (new \SplFileObject(__DIR__.'/../filtered.csv'))->getRealPath();
    }

    public function getEmptyFile(): string
    {
        return (new \SplFileObject(__DIR__.'/../empty.csv'))->getRealPath();
    }

    public function getWithHeaderFile(): string
    {
        return (new \SplFileObject(__DIR__.'/../with-headers.csv'))->getRealPath();
    }

    public function getWithoutHeaderFile(): string
    {
        return (new \SplFileObject(__DIR__.'/../without-headers.csv'))->getRealPath();
    }
}
