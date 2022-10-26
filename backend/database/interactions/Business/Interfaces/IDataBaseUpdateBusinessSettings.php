<?php

namespace Database\Interactions\Business\Interfaces;

interface IDataBaseUpdateBusinessSettings
{
    /**
     * @param array $attributes
     * @return void
     *
     * @throws \Throwable
     */
    public function update(array $attributes): void;
}
