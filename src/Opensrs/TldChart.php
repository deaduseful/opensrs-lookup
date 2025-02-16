<?php

namespace Deaduseful\Opensrs;

class TldChart
{
    /**
     * @var string URL to fetch data from.
     * @see http://opensrs.help/chart
     */
    const SOURCE = 'https://docs.google.com/spreadsheets/d/13t4l-kO3qAio4RCF3j1lF0X2AxaIr_G5kFIqIF3LAZU/export?gid=1203932299&format=csv';

    /** @var array Source as data array. */
    protected array $data;

    /**
     * TldChart constructor.
     */
    public function __construct(?string $source = null)
    {
        $data = $this->fetch($source);
        $this->setData($data);
    }

    public static function fetch(?string $source = null): array
    {
        $source = $source ?: self::SOURCE;
        $array = [];
        $fh = fopen($source, 'r');
        while (($data = fgetcsv($fh)) !== false) {
            $array[] = $data;
        }
        fclose($fh);
        return $array;
    }

    /**
     * Get the Tlds only from the data.
     */
    public function getTlds(): array
    {
        $tlds = [];
        foreach ($this->data as $row) {
            if ($row[0]) {
                $tlds[] = $row[0];
            }
        }
        array_shift($tlds);
        return $tlds;
    }

    public function getDataByTld(string $tld): array
    {
        $data = $this->getData();
        foreach ($data as $item) {
            if ($item[0] === $tld) {
                return $item;
            }
        }
        return [];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getSuffixes(): array
    {
        $column = 16;
        $data = $this->getData();
        $results = [];
        foreach ($data as $item) {
            if ($item[$column]) {
                $suffixes = explode(',', $item[$column]);
                foreach ($suffixes as $suffix) {
                    $results[] = trim($suffix);
                }
            } else {
                $results[] = $item[0];
            }
        }
        array_shift($results);
        return $results;
    }
}

