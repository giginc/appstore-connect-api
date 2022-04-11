<?php
namespace Giginc\AppStore\Api;

class SalesReports extends AbstractApi
{
    public function query(array $params = [])
    {
        return $this->get('/salesReports', $params, [], true);
    }
}
