<?php
namespace Giginc\AppStore\Api;

class AppInfo extends AbstractApi
{
    public function all(array $params = [])
    {
        return $this->get('/appInfo', $params);
    }

    public function query(int $id, array $params = [])
    {
        return $this->get("/appInfo/{$id}/appInfoLocalizations", $params);
    }
}
