<?php

namespace Deaduseful\Opensrs;

class DomainPricing
{
    /** @var string URL to fetch data from. */
    const SOURCE = 'https://opensrs.com/wp-admin/admin-ajax.php';
    const SOURCE_DATA = 'action=angus_get_dataset&data=eyJvcHRpb25LZXkiOiJhbmd1c19zeW5jXzBhNmNiNzJkLTE3ZjMtNDFhZC05Y2E5LWQ1MjM1NzliM2U4MSIsInRsZHMiOmZhbHNlfQ';

    /** @var array Source as data array. */
    private $data;

    public function __construct()
    {
        $data = self::fetch();
        $this->setData($data);
    }

    public static function fetch(): array
    {
        $headers = [];
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers = implode(PHP_EOL, $headers);
        $json = Request::filePostContents(self::SOURCE, self::SOURCE_DATA, $headers);
        $data = json_decode($json, true);
        return $data['data'][0];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Get the Tlds only from the data.
     */
    public function getTlds(): array
    {
        $tlds = [];
        foreach ($this->data as $row) {
            if ($row[0]) {
                $tlds[] = trim($row[0], '.');
            }
        }
        array_shift($tlds);
        return $tlds;
    }

    public function getDataByTld(string $tld): array
    {
        $data = $this->toArray();
        return $data[$tld] ?? [];
    }

    public function toArray(): array
    {
        $data = $this->getData();
        $list = [];
        foreach ($data as $item) {
            $key = $item['tld'];
            $list[$key] = $item;
        }
        return $list;
    }
}

