<?php

namespace GlebecV\Repository;

use GlebecV\RepositoryInterface;

class CsvRepository implements RepositoryInterface
{

    /**
     * @return array
     */
    public function getHostCollection(): array
    {
        return [];
    }

    public function setup(array $config): void
    {
        // TODO: Implement setup() method.
    }
}