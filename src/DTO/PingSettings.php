<?php

namespace GlebecV\DTO;

class PingSettings
{
    public $timeout;
    public $count;
    public $attempts;
    public $prompt;
    public $host;

    public function __construct(
        int $timeout,
        int $count,
        int $attempts,
        string $prompt = '#',
        string $host = null
    )
    {
        $this->timeout    = $timeout;
        $this->count      = $count;
        $this->attempts   = $attempts;
        $this->prompt     = $prompt;
        $this->host       = $host;
    }
}