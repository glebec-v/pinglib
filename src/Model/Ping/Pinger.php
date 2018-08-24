<?php

namespace GlebecV\Model\Ping;

use GlebecV\HostInterface;
use GlebecV\PingInterface;

class Pinger implements PingInterface
{

    /**
     * Model constructor.
     */
    public function __construct()
    {}


    /**
     * @param HostInterface $host
     * @param int $count
     * @param int $timeout
     * @param int $attempts
     * @return array
     */
    public function ping(HostInterface $host, int $count, int $timeout, int $attempts): array
    {
        $counter = 0;
        do {
            $counter++;
            exec(sprintf('ping -c %d -W %d %s', $count, $timeout, escapeshellarg($host->ipAddress())), $res, $rval);
            if ($attempts === $counter) {
                break;
            }
        } while (0 !== $rval);

        return [
            'ok'       => $rval === 0,
            'res'      => !empty($res[1] ?? '') ? $res[1] : '',
            'attempts' => $counter
        ];
    }

    public function connect(string $sourceHost, int $port = 23, string $prompt = '#'): void
    {}
}