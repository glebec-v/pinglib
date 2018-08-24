<?php

namespace GlebecV;

interface HostInterface
{
    public function ipAddress(): string;
    public function vlan(): ?int;
    public function toArray(): array;
}