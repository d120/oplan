<?php

namespace Oplan\Http\Middleware;

use Closure;

use Oplan\Veranstaltung;

class VeranstaltungMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        $veranst = Veranstaltung::byKey($request->route('ver_k'));
        if (!$veranst) return response()->json(['success' => false, 'msg' => 'unbekannte veranstaltung'])->setStatusCode(404);
        
        $request->veranst = $veranst;
        
        return $next($request);
    }
}
