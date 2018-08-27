<?php

namespace GlebecV\Repository;

use GlebecV\Model\Host;
use GlebecV\RepositoryInterface;
use League\Csv\Reader;

class CsvRepository implements RepositoryInterface
{
    /** @var Reader $reader */
    private $reader;

    /**
     * @return array
     * @throws \League\Csv\Exception
     */
    public function getHostCollection(): array
    {
        $ret = [];

        $this->reader->setHeaderOffset(0);
        $records = $this->reader->getRecords();
        $header = $this->reader->getHeader();
        if (isset($header['ip'])) {
            foreach ($records as $record) {
                $ret[] = new Host(
                    $record['ip'],
                    $record['serial'] ?? null,
                    $record['name'] ?? null,
                    $record['vlan'] ?? null
                );
            }
        }

        return $ret;
    }

    public function setup(array $config): void
    {
        if (!isset($config['file'])) {
            // todo change to own exception
            throw new \InvalidArgumentException('Source file is not defined', 1);
        }
        $this->reader = Reader::createFromPath($config['file'], 'r');
    }
}