<?php

namespace GlebecV\Model;

use GlebecV\DTO\PingSettings;
use GlebecV\Model\Ping\Pinger;
use GlebecV\Model\Ping\UhpHubPinger;
use GlebecV\PingInterface;
use GlebecV\RepositoryInterface;
use GlebecV\UpStationRepository;

use Monolog\Logger;

class PingContext
{
    public const PING_METHODS = [
        'direct' => Pinger::class,
        'cross'  => UhpHubPinger::class,
    ];

    private $pinger;
    private $settings;
    private $logger;
    private $repository;

    public function __construct(
        PingSettings $settings,
        RepositoryInterface $repository,
        Logger $logger,
        string $method
    )
    {
        $this->repository = $repository;
        $this->logger     = $logger;
        $this->settings   = $settings;
        $this->pinger     = $this->createPinger($method);
        $this->pinger->connect($this->settings->host, 23, $this->settings->prompt);
    }

    public function permanentPings()
    {
        $pingCycleCount = 1;
        while (true) {
            $collection = $this->repository->getHostCollection();
            $countUp = $totalStations = count($collection);
            $countSuccess = 0;
            try {
                $this->execute($collection, $countUp, $countSuccess);
                $this->logger->notice("Ping cycle {$pingCycleCount} finished: {$countSuccess} responded of {$countUp} stations UP of {$totalStations} total filtered");
            } catch (\Exception $exception) {
                $this->logger->critical('Pinger interrupted... Unable to continue', ['pinger-message' => $exception->getMessage()]);
            }
            $pingCycleCount++;
        }
    }

    public function pings()
    {
        $collection = $this->repository->getHostCollection();
        $countUp = $totalStations = count($collection);
        $countSuccess = 0;
        try {
            $this->execute($collection, $countUp, $countSuccess);
            $this->logger->notice("Ping finished: {$countSuccess} responded of {$countUp} stations UP of {$totalStations} total filtered");
        } catch (\Exception $exception) {
            $this->logger->critical('Pinger interrupted... Unable to continue', ['pinger-message' => $exception->getMessage()]);
        }
    }

    private function execute($collection, &$up, &$success)
    {
        foreach ($collection as $counter => $item) {
            /** @var Host $item */
            if ($collection instanceof UpStationRepository && !$collection->isUp($item)) {
                $this->logger->warning((string)$counter.' DOWN', ['target' => $item->toArray()]);
                $up--;
                continue;
            }

            $result = $this->pinger->ping($item, $this->settings->count, $this->settings->timeout, $this->settings->attempts);
            if ($result['ok']) {
                $this->logger->info((string)$counter, ['target' => $item->toArray(), 'result' => $result['res'], 'attempts' => $result['attempts']]);
                $success++;
            } else {
                $this->logger->error((string)$counter, ['target' => $item->toArray(), 'attempts' => $result['attempts']]);
            }
        }
    }

    private function createPinger(string $method): PingInterface
    {
        if (!in_array($method, array_keys(self::PING_METHODS))) {
            throw new \InvalidArgumentException("{$method} is not supported", 1);
        }
        $methods = self::PING_METHODS;
        return new $methods[$method]();
    }
}