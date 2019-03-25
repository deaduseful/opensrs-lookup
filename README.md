# OpenSRS Lookup for PHP

A simple Tucows OpenSRS lookup API for PHP.

## Installation

`composer require deaduseful/opensrs-lookup`

## Usage

```php
    function checkAvailability($query)
    {
        $lookup = new DeadUseful\Opensrs\FastLookup($query);
        $result = $lookup->getResult();
        if ($result['status'] === 'taken') {
            return false;
        }
        if ($result['status'] === 'available') {
            return true;
        }
        throw new Exception('No result.');
    }
```