<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ApplyPublicPathPrefix
{
    private const PREFIX = '/8090';

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.ltrim($request->getPathInfo(), '/');

        if ($path === self::PREFIX || str_starts_with($path, self::PREFIX.'/')) {
            $host = $request->getSchemeAndHttpHost();
            URL::forceRootUrl($host.self::PREFIX);

            $strippedPath = substr($path, strlen(self::PREFIX)) ?: '/';
            $query = $request->getQueryString();
            $requestUri = $strippedPath.($query ? '?'.$query : '');

            $request->server->set('REQUEST_URI', $requestUri);
            $request->server->set('PATH_INFO', $strippedPath);
        }

        return $next($request);
    }
}
