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
    public function renew(array $attributes = [])
    {
        $attributes['currentexpirationyear'] = substr($attributes['registry_expiredate'], 0, 4);
        return $this->perform(self::ACTION, $attributes);
    }
}
