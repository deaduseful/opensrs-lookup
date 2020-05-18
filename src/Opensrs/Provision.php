<?php

namespace Deaduseful\Opensrs;

class Provision extends Service
{
    const ACTION_REGISTER = 'sw_register';

    /**
     * Register.
     * @param array $data
     * @return array
     */
    public function register(array $data = [])
    {
        return $this->perform(self::ACTION_REGISTER, $data);
    }
}
