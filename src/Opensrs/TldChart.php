<?php

namespace Deaduseful\Opensrs;

class TldChart
{
    /** @var string URL to fetch data from. */
    const SOURCE = 'https://docs.google.com/spreadsheets/d/13t4l-kO3qAio4RCF3j1lF0X2AxaIr_G5kFIqIF3LAZU/export?format=csv';

    /** @var array Source as data array. */
    private $data;

    /**
     * TldChart constructor.
     */
    public function __construct()
    {
        $this->data = $this->fetch();
    }

    /**
     * @return array
     */
    private function fetch()
    {
        $array = [];
        $fh = fopen(self::SOURCE, 'r');
        while (($data = fgetcsv($fh)) !== false) {
            $array[] = $data;
        }
        fclose($fh);
        return $array;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Get the Tlds only from the data.
     *
     * @return array
     */
    public function getTlds()
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

    /**
     * @param $tld
     * @return mixed
     */
    public function getDataByTld($tld) {
        $data = $this->getData();
        foreach ($data as $item) {
            if ($item[0] === $tld) {
                return $tld;
            }
        }
        return [];
    }

    public function getSuffixes() {
        $data = $this->getData();
        $results = [];
        foreach ($data as $item) {
            if ($item[5]) {
                $suffixes = explode(',', $item[5]);
                foreach ($suffixes as $suffix) {
                    $results[] = trim($suffix);
                }
            } else {
                $results[] = $item[0];
            }
        }
        return $results;
    }
}

