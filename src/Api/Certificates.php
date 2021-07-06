<?php

namespace Giginc\AppStore\Api;


class Certificates extends AbstractApi
{
    public function all(array $params = [])
    {
        return $this->get('/certificates', $params);
    }
}
