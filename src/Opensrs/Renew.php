<?php

namespace Deaduseful\Opensrs;

class Renew extends Service
{
    const ACTION= 'renew';

    /**
     * Register.
     * @param array $attributes
     * @return array
     */
    public function renew(array $attributes = []): array
    {
        return $this->perform(self::ACTION, $attributes);
    }
}
