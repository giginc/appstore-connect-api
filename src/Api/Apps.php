<?php
namespace Giginc\AppStore\Api;

class Apps extends AbstractApi
{
    public function all(array $params = [])
    {
        return $this->get('/apps', $params);
    }

    public function query(int $id, array $params = [])
    {
        return $this->get("/apps/{$id}/appInfos", $params);
    }
}
