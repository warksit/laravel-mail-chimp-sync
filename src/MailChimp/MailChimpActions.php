<?php namespace Warksit\LaravelMailChimpSync\MailChimp;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\RequestException;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpAuthNotSet;

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
     * MailChimpActions constructor.
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @param Model $model
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function ping(Model $model)
    {
        $this->setAuth($model->getMailChimpAuth());

        return $this->process('GET','/3.0/');
    }

    /**
     * @param $method
     * @param $uri
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws MailChimpAuthNotSet
     * @throws MailChimpException
     */
    protected function process($method, $uri, $data = [])
    {
        if( !$this->auth || !$this->endpoint )
            throw new MailChimpAuthNotSet();

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

    /**
     * @param MailChimpAuth $auth
     */
    protected function setAuth(MailChimpAuth $auth)
    {
        $this->auth = $auth->authHeader();
        $this->endpoint = $auth->getEndpoint();
    }
}