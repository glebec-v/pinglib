<?php

namespace GlebecV;

interface UpStationRepository extends RepositoryInterface
{
    public function isUp(HostInterface $host): bool;
}