<?php
namespace IslamicNetwork\Waf\Model;

use GuzzleHttp\Client;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Zend\Diactoros\ServerRequestFactory;

class Proxy
{
    private $response;

    public function __construct($url)
    {
        // Create a PSR7 request based on the current browser request.
        $request = ServerRequestFactory::fromGlobals();

        // Create a guzzle client
        $guzzle = new Client();

        // Create the proxy instance
        $proxy = new \Proxy\Proxy(new GuzzleAdapter($guzzle));

        // Add a response filter that removes the encoding headers.
        $proxy->filter(new RemoveEncodingFilter());

        // Forward the request and get the response.
        $this->response = $proxy
            ->forward($request)
            ->filter(function ($request, $response, $next) {
                // Manipulate the request object.
                $request = $request->withHeader('User-Agent', 'Islamic.Network/WAF/1.0');

                // Call the next item in the middleware.
                $response = $next($request, $response);

                // Manipulate the response object.
                $response = $response->withHeader('X-WAF', 'VESICA-WAF');

                return $response;
            })
            ->to($url);

    }

    public function emit()
    {
        return new \Zend\HttpHandlerRunner\Emitter\SapiEmitter($this->response);

    }

    public function getResponse()
    {
        return $this->response;
    }
}