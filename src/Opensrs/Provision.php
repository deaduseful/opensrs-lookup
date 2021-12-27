<?php

namespace Deaduseful\Opensrs;

class Provision extends Service
{
    const ACTION = 'sw_register';

    /**
     * Register.
     * @param array $attributes
     * @return array
     */
    public function register(array $attributes = []): array
    {
        return $this->perform(self::ACTION, $attributes);
    }
}
