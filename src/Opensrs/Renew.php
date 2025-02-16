<?php

namespace Deaduseful\Opensrs;

class Renew extends Service
{
    public const ACTION = 'renew';

    /**
     * Renew.
     */
    public function renew(array $attributes = []): array
    {
        return $this->perform(self::ACTION, $attributes);
    }
}
