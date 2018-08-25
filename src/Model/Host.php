<?php

namespace GlebecV\Model;

use GlebecV\HostInterface;

class Host implements HostInterface
{
    public $ip;
    public $serial;
    public $name;
    public $vlan;

    /**
     * StationCollection constructor.
     * @param string $ip
     * @param string|null $serial
     * @param string|null $name
     * @param int|null    $vlan
     */
    public function __construct(string $ip, string $serial = null, string $name = null, int $vlan = null)
    {
        $this->ip = $ip;
        $this->serial = $serial;
        $this->name = $name;
        $this->vlan = $vlan;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $ret = ['ip' => $this->ip]; // required field

        if (!is_null($this->serial)) {
            $ret['serial'] = $this->serial;
        }
        if (!is_null($this->name)) {
            $ret['name'] = $this->name;
        }
        if (!is_null($this->vlan)) {
            $ret['vlan'] = $this->vlan;
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function ipAddress(): string
    {
        return $this->ip;
    }

    /**
     * @return int|null
     */
    public function vlan(): ?int
    {
        return $this->vlan;
    }
}