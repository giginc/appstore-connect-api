<?php
namespace Giginc\AppStore\Api;

class FinanceReports extends AbstractApi
{
    public function query(array $params = [])
    {
        return $this->get('/financeReports', $params);
    }
}
