<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\Exceptions;
use App\Repositories\ClientRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientMiddleware
{
    private $clientRepo;

    public function __construct(ClientRepository $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    public function handle(Request $request, Closure $next)
    {
        $clientId = $request->header('Client-ID');
        
        if($clientId) {
            $clients = $this->clientRepo->all();
            foreach($clients as $client) {
                if (Hash::check($clientId, $client->client_id)) {
                    return $next($request);
                }
            }

            Exceptions::unauthenticatedException(INVALID_CLIENT_ID);
        }
        else {
            Exceptions::unauthenticatedException(CLIENT_ID_REQUIRED);
        }
    }
}