<?php


namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Repositories\ClientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function generate(Request $request)
    {
        $productName = $request->input('product_name');
        $platform = $request->input('platform');

        $clientId = str_random(60);
        $hashedClientId = Hash::make($clientId);

        $data = [
            'client_id' => $hashedClientId,
            'product_name' => $productName,
            'platform' => $platform
        ];
        $client = $this->clientRepo->create($data);

        // Change the client id of the response to the non-hashed version
        $data = $client->toArray();
        $data['client_id'] = $clientId;
        array_pull($data, 'id');

        return Response::dataResponse(true, [
            'client' => $data
        ]);
    }
}