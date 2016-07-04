<?php namespace Warksit\LaravelMailChimpSync\MailChimp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Config\Repository;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;

/**
 * Class MailChimpActions
 * @package Warksit\MailingList\MailChimp
 */
class MailChimpActions
{
    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var String
     */
    protected $endpoint;

    /**
     * @var array
     */
    protected $auth;

    /**
     * @var array
     */
    private $auth_type = [
        'basic' => 'maillist.auths.basic.api_key',
        'oauth' => 'maillist.auths.oauth.access_token',
    ];
    /**
     * @var Repository
     */
    private $config;

    /**
     * MailChimpActions constructor.
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle, Repository $config)
    {
        $this->guzzle = $guzzle;

        if ( ! array_key_exists($config->get('maillist.default'),$this->auth_type))
            throw new \InvalidArgumentException("Mailing List Auth type not set correctly {{$config->get('maillist.default')}}");

        $this->config = $config;
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws MailChimpException
     */
    public function ping()
    {
        return $this->process('GET','/3.0/');
    }

    /**
     * @param $method
     * @param $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws MailChimpException
     */
    protected function process($method, $uri, $data = [])
    {
        // Done here rather than constructor to allow for
        // config to be set at runtime

        $this->setConfig();

        try {
            return $this->guzzle->request(
                $method,
                $this->endpoint . $uri,
                array_merge($this->auth,$data)
            );
        } catch (RequestException $e) {
            $message = ($e->hasResponse()) ?  ' due to ' . \GuzzleHttp\Psr7\str($e->getResponse()) : '[No MailChimp Message]';
            throw new MailChimpException('Error: '. $method . 'ing to ' . $this->endpoint . $uri . ' due to ' . $message);
        }
    }

    private function setConfig()
    {
        // This works for basic auth too as MailChimp allow any username

        $this->auth = [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->config->get($this->auth_type[$this->config->get('maillist.default')]),
            ],
        ];
        $this->endpoint = $this->config->get('maillist.endpoint');
    }
}