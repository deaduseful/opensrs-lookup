<?php

namespace Deaduseful\Opensrs;

class DomainPricing
{
    /** @var string URL to fetch data from. */
    protected const SOURCE = 'https://opensrs.com/wp-admin/admin-ajax.php';

    protected const SOURCE_QUERY = [
        'action' => 'tt_get_dataset',
        'data' => 'eyJvcHRpb25LZXkiOiJhbmd1c19zeW5jXzBhNmNiNzJkLTE3ZjMtNDFhZC05Y2E5LWQ1MjM1NzliM2U4MSIsInRsZHMiOmZhbHNlfQ=='
    ];

    protected const HEADERS = [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: php/' . PHP_VERSION,
    ];

    /** @var array Source as data array. */
    protected array $data;
    protected RequestClient $request;

    public function __construct(RequestClient $request = null)
    {
        $this->request = $request ?? new RequestClient();
        $data = $this->fetch();
        $this->data = $this->getParsedData($data);
    }

    protected function fetch(): array
    {
        $headers = implode(PHP_EOL, self::HEADERS);
        $content = http_build_query(self::SOURCE_QUERY);
        $json = $this->request->filePostContents(self::SOURCE, $content, $headers);
        return json_decode($json, true);
    }

    public function getData(): array
    {
        return $this->data;
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

    public function getByTld(string $tld): ?DomainPrice
    {
        $data = $this->data;
        return $data[$tld] ?? null;
    }

    public function toArray(): array
    {
        return $this->getData();
    }

    protected function getParsedData(array $data): array
    {
        $parsedData = $data['data'][0];
        $list = [];
        foreach ($parsedData as $item) {
            $domainPrice = new DomainPrice($item);
            $key = $domainPrice->getTld();
            $list[$key] = $domainPrice;
        }
        return $list;
    }
}
