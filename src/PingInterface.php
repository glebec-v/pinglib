<?php

namespace GlebecV;

interface PingInterface
{
    public function connect(string $sourceHost = null, int $port = 23, string $prompt = '#'): void;
    public function ping(HostInterface $host, int $count, int $timeout, int $attempts): array;
}