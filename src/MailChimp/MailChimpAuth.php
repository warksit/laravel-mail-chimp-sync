<?php namespace Warksit\LaravelMailChimpSync\MailChimp;

class MailChimpAuth
{
    private $endpoint;
    private $api;

    /**
     * MailChimpAuth constructor.
     * @param $endpoint
     * @param $api
     */
    public function __construct($endpoint, $api)
    {
        $this->endpoint = $endpoint;
        $this->api = $api;
    }

    public function authHeader()
    {
        return [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->api,
            ],
        ];
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }


}