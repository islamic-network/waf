<?php

namespace Vesica\Waf\Exceptions;

use Psr\Log\LogLevel;
use Slim\Http\Request;
use Slim\Http\Response;

class Handler
{
    public function __invoke(Request $request, Response $response, \Exception $exception = null) {

        if ($exception instanceof BlackListException) {
            $response = $response->withHeader('X-WAF-STATUS', 'BLACKLISTED');
            return $response->withJson(self::blacklist(), 403);
        }

        if ($exception instanceof RateLimitException) {
            $response = $response->withHeader('X-WAF-STATUS', 'RATELIMITED');
            return $response->withJson(self::ratelimit(), 429);
        }


        $r = [
            'code' => 500,
            'status' => 'Internal Server Error',
            'data' => 'Something went wrong when the server tried to process this request. Sorry!'
        ];

        $logger = new \Monolog\Logger('AlAdhanApi/WAF');
        $logger->pushHandler( new \Monolog\Handler\StreamHandler('php://stdout', LogLevel::ERROR));
        $logger->error( $exception->getCode() . ' : ' . $exception->getMessage() . ' | ' . $exception->getTraceAsString());

        return $response->withJson($r, 500);
    }

    public function blacklist(): array
    {
        return [
            'code' => 403,
            'status' => 'Forbidden',
            'data' => 'You are on the Blacklist. If you think this is an error, please contact us via the information on https://aladhan.com/contact.'
        ];
    }

    public function ratelimit(): array
    {
        return [
            'code' => 429,
            'status' => 'Too Many Requests',
            'data' => 'You have been rate limited temporarily. If you think this is an error or your use is genuine, please contact us via the information on https://aladhan.com/contact.'
        ];
    }
}
