<?php
namespace Giginc\AppStore;

use Giginc\AppStore\Exceptions\ConfigException;
use Giginc\AppStore\Exceptions\InvalidArgumentException;
use Giginc\AppStore\Utils\HttpClient;
use Giginc\AppStore\Utils\JWT;

class Client
{
    use HttpClient;

    const BASE_URI = 'api.appstoreconnect.apple.com';

    const JWT_AUD = 'appstoreconnect-v1';

    const JWT_ALG = 'ES256';

    private $apiVersion;

    private $config;

    private $iss;

    private $kid;

    private $secret;

    private $headers;


    public function __construct(array $config)
    {
        $this->config = $config;
        $this->iss = !array_key_exists('iss', $config) ?: $config['iss'];
        $this->kid = !array_key_exists('kid', $config) ?: $config['kid'];
        $secret = !array_key_exists('secret', $config) ?: $config['secret'];
        $this->setSecret($secret);
        $this->apiVersion = !array_key_exists('apiVersion', $config) ? 'v1' : $config['apiVersion'];
    }

    public function getIss()
    {
        return $this->iss;
    }

    public function setIss($iss)
    {
        $this->iss = $iss;
    }

    public function getKid()
    {
        return $this->kid;
    }

    public function setKid($kid)
    {
        $this->kid = $kid;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        if (file_exists($secret)) {
            $this->secret = file_get_contents($secret);
        } else {
            $this->secret = $secret;
        }
    }

    public function setToken($jwtToken)
    {
        $this->headers['Authorization'] = 'Bearer ' . $jwtToken;
    }

    public function getToken()
    {
        if (!$this->iss || !$this->kid || !$this->secret) {
            throw new ConfigException('Required settings are missing.');
        }
        return $this->generateJwt();
    }

    public function checkAuthHeader()
    {
        if (empty($this->headers) || !array_key_exists('Authorization', $this->headers)) {
            $jwtToken = $this->getToken();
            $this->headers['Authorization'] = 'Bearer ' . $jwtToken;
        }
    }

    public function setHeaders(array $headers)
    {
        if (!$this->headers) {
            $this->headers = [];
        }
        $this->headers = array_merge($this->headers, $headers);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    protected function generateJwt()
    {
        $payload = $this->buildPayload();
        $header = $this->buildHeader();
        $secret = $this->secret;

        return JWT::encode($payload, $header, $secret);
    }

    private function buildPayload()
    {
        return [
            'iss' => $this->getIss(),
            'exp' => time() + 20 * 60,
            'aud' => static::JWT_AUD
        ];
    }

    private function buildHeader()
    {
        return [
            'kid' => $this->getKid(),
            'alg' => static::JWT_ALG,
            'typ' => 'JWT'
        ];
    }

    public function buildBaseUrl()
    {
        return sprintf('https://%s/%s', static::BASE_URI, $this->apiVersion);
    }

    public function api($name)
    {
        switch ($name) {
            case 'apps':
                $api = new Api\Apps($this);
                break;
            case 'appInfo':
                $api = new Api\AppInfo($this);
                break;
            case 'device':
                $api = new Api\Device($this);
                break;
            case 'bundleId':
                $api = new Api\BundleId($this);
                break;
            case 'bundleIdCapabilities':
                $api = new Api\BundleIdCapability($this);
                break;
            case 'profiles':
                $api = new Api\Profiles($this);
                break;
            case 'certificates':
                $api = new Api\Certificates($this);
                break;
            case 'salesReports':
                $api = new Api\SalesReports($this);
                break;
            case 'financeReports':
                $api = new Api\FinanceReports($this);
                break;
            default:
                throw new InvalidArgumentException('Not found api connection');
        }

        return $api;
    }
}
