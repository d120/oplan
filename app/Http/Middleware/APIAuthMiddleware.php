<?php

namespace Oplan\Http\Middleware;

use Closure;
use Oplan\User;
use Oplan\Veranstaltung;

use Illuminate\Contracts\Auth\Guard;

class APIAuthMiddleware
{
  
      /**
       * The guard instance.
       *
       * @var \Illuminate\Contracts\Auth\Guard
       */
      protected $auth;
  
      /**
       * Create a new middleware instance.
       *
       * @param  \Illuminate\Contracts\Auth\Guard  $auth
       * @return void
       */
      public function __construct(Guard $auth)
      {
          $this->auth = $auth;
      }
  
      /**
       * Handle an incoming request.
       *
       * @param  \Illuminate\Http\Request  $request
       * @param  \Closure  $next
       * @return mixed
       */
      public function handle($request, Closure $next)
      {
          $cred = ['name' => $request->getUser(), 'password' => $request->getPassword(), 'veranstaltung_id' => null];
          if ($this->auth->once($cred)) {
              return $next($request);
          }
          if ($request->veranst) {
              $cred['veranstaltung_id'] = $request->veranst->id;
              if ($this->auth->once($cred)) {
                  return $next($request);
              }
          }
          $response = response()->json(['success' => false, 'error' => 'Unauthorized! Please sign in to perform this action.'])->setStatusCode(401);
          $realm = "Oplan";
          if ($request->veranst) $realm = "Oplan ".$request->veranst->kuerzel;
          $response->headers->set('WWW-Authenticate', 'Basic realm="'.$realm.'"');
          return $response;
      }
}
