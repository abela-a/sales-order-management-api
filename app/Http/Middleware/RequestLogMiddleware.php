<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $contents = json_decode($response->getContent(), true, 512);

        $headers = $request->header();

        $date = new Carbon;
        $data = [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'ip' => $request->ip(),
            'http_version' => $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
            'timestamp' => $date->toDateTimeString(),
            'headers' => [
                'user-agent' => $headers['user-agent'],
                'referer' => $headers['referer'] ?? null,
                'origin' => $headers['origin'] ?? null,
            ],
        ];

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        if (count($request->all()) > 0) {
            $hiddenKeys = ['password'];

            $data['request'] = $request->except($hiddenKeys);
        }

        if (! empty($contents['message'])) {
            $data['response']['message'] = $contents['message'];
        }

        if (! empty($contents['errors'])) {
            $data['response']['errors'] = $contents['errors'];
        }

        if (! empty($contents['result'])) {
            $data['response']['result'] = $contents['result'];
        }

        $message = str_replace('/', '_', trim($request->getPathInfo(), '/'));

        Log::channel('request')->info($message, $data);

        return $response;
    }
}
