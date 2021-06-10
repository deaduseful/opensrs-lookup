<?php

namespace Deaduseful\Opensrs;

class TldChart
{
    /**
     * @var string URL to fetch data from.
     * @see http://opensrs.help/chart
     */
    const SOURCE = 'https://docs.google.com/spreadsheets/d/13t4l-kO3qAio4RCF3j1lF0X2AxaIr_G5kFIqIF3LAZU/export?format=csv';

    /** @var array Source as data array. */
    private $data;

    /**
     * TldChart constructor.
     *
     * @param string|null $source the file or url of the source
     */
    public function __construct($source = null)
    {
        $data = $this->fetch($source);
        $this->setData($data);
    }

    /**
     * @param string|null $source the file or url of the source
     * @return array
     */
    public static function fetch($source = null)
    {
        $source = $source ? $source : self::SOURCE;
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
    public function getDataByTld($tld)
    {
        $data = $this->getData();
        foreach ($data as $item) {
            if ($item[0] === $tld) {
                return $tld;
            }
        }
        return [];
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

    public function getSuffixes()
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

