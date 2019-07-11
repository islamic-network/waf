<?php
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;

$app->get('/[{path:.*}]', function (Request $request, Response $response, $args) {

    // Create a guzzle client
    $guzzle = new Client();

    // Create the proxy instance
    $proxy = new \Proxy\Proxy(new GuzzleAdapter($guzzle));

    // Add a response filter that removes the encoding headers.
    $proxy->filter(new RemoveEncodingFilter());

    try {
        // Forward the request and get the response.
        $response = $proxy
            ->forward($request)
            ->filter(function (Request $request, Response $response, $next) {
                // Manipulate the request object.
                $request = $request->withHeader('X-Forwarded-For', $request->getHeader('X-Forwarded-For'));

                // Call the next item in the middleware.
                $response = $next($request, $response);

                return $response;
            })
            ->to($this->proxy->url);


        return $response;

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        return $e->getResponse();
    }

});
