<?php

namespace Giginc\AppStore\Utils;

use Curl\Curl;
use Giginc\AppStore\Exceptions;

trait HttpClient
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * getCurl
     *
     * @access protected
     * @return void
     */
    protected function getCurl()
    {
        $this->curl = new Curl();
        return $this;
    }

    /**
     * get
     *
     * @param string $url 
     * @param array $params 
     * @param array $headers 
     * @param bool $isResponseTsv 
     * @access public
     * @return void
     */
    public function get($url, array $params = [], array $headers = [], bool $isResponseTsv = false)
    {
        $this->getCurl();
        foreach ($headers as $key => $value) {
            $this->curl->setHeader($key, $value);
        }
        $this->curl->get($url, $params);

        if ($this->curl->getHttpStatusCode() == 200) {
            return $isResponseTsv ? $this->wrapTsvContent($this->curl->getResponse()) :$this->wrapContent($this->curl->getResponse());
        } else {
            return false;
        }
    }

    /**
     * postJson
     *
     * @param string $url 
     * @param array $body 
     * @param array $headers 
     * @access public
     * @return void
     */
    public function postJson($url, array $body = [], array $headers = [])
    {
        $this->getCurl();
        foreach ($headers as $key => $value) {
            $this->curl->setHeader($key, $value);
        }
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->post($url, $body);
        return $this->wrapContent($this->curl->getResponse());
    }

    public function delete($url, array $params = [], array $headers = [])
    {
        $this->getCurl();
        foreach ($headers as $key => $value) {
            $this->curl->setHeader($key, $value);
        }
        $this->curl->delete($url, $params);
        return $this->wrapContent($this->curl->getResponse());
    }

    protected function wrapContent($content)
    {
        if (is_string($content)) {
            $content = json_decode(implode('', explode(PHP_EOL, $content)));
        }
        return json_decode(json_encode($content), true);
    }

    /**
     * wrapTsvContent
     *
     * @param string $content 
     * @access protected
     * @return void
     */
    protected function wrapTsvContent(string $content)
    {
        $response = null;

        try {
            $data = gzdecode($content);
            if (!isset($data) || !$data) {
                return null;
            }

            $rows = explode("\n", $data);
            $headers = explode("\t", array_shift($rows));


            foreach ($rows as $values) {
                if (empty($values)) {
                    continue;
                }

                $data = explode("\t", $values);

                $response[] = array_combine(
                    $headers,
                    $data
                );
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $response;
    }
}
