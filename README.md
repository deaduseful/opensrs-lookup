# OpenSRS Lookup for PHP

A simple Tucows OpenSRS lookup API for PHP.

## Installation

`composer require deaduseful/opensrs-lookup`

## Usage

### FastLookup
```php
    /**
     * @param $query
     * @return bool
     * @throws Exception
     */
    function checkAvailability($query)
    {
        $lookup = new Deaduseful\Opensrs\FastLookup($query);
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
### Lookup
```php
    function checkTransferable($query)
    {
        $lookup = new Deaduseful\Opensrs\Lookup($query, 'check_transfer');
        $result = $lookup->getResult();
        return $result['transferrable'] === 1;
    }
```

### About

- A [deaduseful](https://deaduseful.com/) project.
- [Made by Wade](https://wade.be/)