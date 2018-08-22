<?php

namespace GlebecV\DTO;

class PingSettings
{
    public $timeout;
    public $count;
    public $attempts;

    public function __construct(
        int $timeout,
        int $count,
        int $attempts
    )
    {
        $this->timeout    = $timeout;
        $this->count      = $count;
        $this->attempts   = $attempts;
    }
}