<?php

namespace GlebecV\Repository;

use GlebecV\Model\Host;
use GlebecV\RepositoryInterface;

class ManualRepository implements RepositoryInterface
{

    /**
     * @return array
     */
    public function getHostCollection(): array
    {
        return [
            new Host('192.168.0.1'),
        ];
    }

    public function setup(array $config): void
    {
        // TODO: Implement setup() method.
    }
}