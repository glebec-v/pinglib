<?php

namespace GlebecV\Repository;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use GlebecV\HostInterface;
use GlebecV\Model\Host;
use GlebecV\UpStationRepository;

/**
 * Class DbRepository
 * @package GlebecV\Repository
 *
 * @property Connection $connection
 * @property array      $config
 */
class DbRepository implements UpStationRepository
{
    private $connection;
    private $config;

    /**
     * @param array $config
     * config should contain several arrays:
     *
     * connection credentials for database
     * -- 'connection' => [
     * -- -- 'dbname'   => 'nms_db',
     *       'user'     => 'root',
     *       'password' => '',
     *       'host'     => 'localhost',
     *       'driver'   => 'pdo_mysql',
     *     ]
     *
     * 'main_sql' query should return next fields at least
     * type string
     *  ... AS ip,     (required)
     *  ... AS serial, (optional)
     *  ... AS name,   (optional)
     *  ... AS vlan,   (optional)
     *
     * if check of host is up required, config should contain 'up_sql'
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \InvalidArgumentException
     */
    public function setup(array $config): void
    {
        $this->connection = DriverManager::getConnection($config['connection'], new Configuration());
        unset($config['connection']);
        if (!isset($config['main_sql'])) {
            throw new \InvalidArgumentException('Missing main sql query', 1);
        }
        $this->config = $config;
    }

    /**
     * @param HostInterface $host
     * @return bool
     */
    public function isUp(HostInterface $host): bool
    {
        if (!isset($config['up_sql'])) {
            throw new \InvalidArgumentException('Missing host is up sql query', 1);
        }
        /** @var Host $host */
        $serial = $host->serial;

        $sth = $this->connection->prepare($this->config['up_sql']);
        $sth->bindValue(1, $serial);
        $sth->execute();
        $result = $sth->fetchAll();

        if (0 !== count($result) && isset($result[0])) {
            return $this->analyzeFaults($result[0]);
        }
        return false;
    }

    /**
     * @return array
     */
    public function getHostCollection(): array
    {
        $ret = [];
        $result = $this->connection->query($this->config['main_sql'])->fetchAll();

        foreach ($result as $item) {
            if (is_null($item['ip'])) {
                continue;
            }

            // if up Host
            if (isset($this->config['up_sql'])) {
                if (!$this->analyzeFaults($item)) {
                    continue;
                }
            }

            // vlan should be int or null
            $vlan = $item['vlan'] ?? null;
            if (!is_null($vlan)) {
                $vlan = (int)$vlan;
            }

            $ret[] = new Host(
                $item['ip'],
                $item['serial'] ?? null,
                $item['name'] ?? null,
                $vlan
            );
        }

        return $ret;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function analyzeFaults(array $data)
    {
        if (0 === (int)$data['enabled']) {
            return false;
        }

        if (is_null($data['down']) || 1 === (int)$data['down']) {
            return false;
        }

        if (0 === (int)$data['down'] && 1 === (int)$data['config']) {
            return true;
        }

        if (1 === (int)$data['rx_only']) {
            return true;
        }

        if (3 === (int)$data['core_state']) {
            // Controlled Unavailable
            return false;
        }

        if (is_null($data['updated'])) {
            return false;
        }

        if (0 !== (int)$data['outdated']) {
            return false;
        }

        return true;
    }
}