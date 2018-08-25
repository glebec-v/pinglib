<?php

namespace GlebecV;

interface RepositoryInterface
{
    /**
     * @return array
     */
    public function getHostCollection(): array;
    public function setup(array $config): void;
}