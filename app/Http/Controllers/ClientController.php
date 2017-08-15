<?php


namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Repositories\ClientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    private $clientRepo;

    public function __construct(ClientRepository $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    /**
     * Generate a client id that is used to access the REST API
     * @return JsonResponse
     */
    public function generate()
    {
        $clientId = str_random(60);
        $hashedClientId = Hash::make($clientId);

        $data = ['client_id' => $hashedClientId];
        $this->clientRepo->create($data);

        return Response::dataResponse(true, [
            'client_id' => $clientId
        ]);
    }
}