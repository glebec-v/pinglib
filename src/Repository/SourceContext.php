<?php

namespace GlebecV\Repository;

use GlebecV\RepositoryInterface;

class SourceContext
{
    private const SOURCE = [
        'db'    => DbRepository::class,
        'csv'   => CsvRepository::class,
        'manual' => ManualRepository::class
    ];

    private $repository;

    public function __construct(string $source, $config)
    {
        $this->repository = $this->createRepo($source);
        $this->repository->setup($config);
    }

    public function getHostsRepository()
    {
        return $this->repository;
    }

    private function createRepo(string $source): RepositoryInterface
    {
        if (!in_array($source, array_keys(self::SOURCE))) {
            throw new \InvalidArgumentException("{$source} is not supported", 1);
        }
        $repositories = self::SOURCE;
        return new $repositories[$source]();
    }

}