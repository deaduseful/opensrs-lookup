<?php

namespace Deaduseful\Opensrs;

class DomainPricing
{
    /** @var string URL to fetch data from. */
    const SOURCE = 'https://opensrs.com/wp-content/uploads/domain_pricing.csv';

    /** @var array Source as data array. */
    private $data;

    /**
     * TldChart constructor.
     *
     * @param string|null $source the file or url of the source
     */
    public function __construct($source = null)
    {
        $source = $source ? $source : self::SOURCE;
        $this->data = $this->fetch($source);
    }

    /**
     * @param string|null $source the file or url of the source
     * @return array
     */
    private function fetch($source = null)
    {
        $array = [];
        $fh = fopen($source, 'r');
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
        $data = $this->toArray();
        return isset($data[$tld]) ? $data[$tld] : [];
    }

    /**
     * @return array
     */
    public function toArray() {
        $data = $this->getData();
        $header = array_shift($data);
        $list = [];
        foreach ($data as $item) {
            $array = array_combine($header, $item);
            $key = $array['tld'];
            $list[$key] = $array;
        }
        return $list;
    }
}

