<?php

namespace App\Http\Controllers;


use App\Exceptions\ResponseHandler as Response;
use App\Repositories\EntityRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class EntityController
 * @package App\Http\Controllers
 */
class EntityController extends Controller
{
    private $entityRepo;

    function __construct(EntityRepository $entityRepo)
    {
        $this->entityRepo = $entityRepo;
    }

    /**
     * Return number of likes of an entity
     * @param $entity_id
     * @return JsonResponse
     */
    public function likesCount($entity_id)
    {
        $entity = $this->entityRepo->read($entity_id);
        $count = $entity->likesCount();
        return Response::dataResponse(true, ['count' => $count]);

    }

    /**
     * Return number of comments of an entity
     * @param $entity_id
     * @return JsonResponse
     */
    public function commentsCount($entity_id)
    {
        $entity = $this->entityRepo->read($entity_id);
        $count = $entity->commentsCount();
        return Response::dataResponse(true, ['count' => $count]);
    }

    /**
     * Return users who liked the entity
     * @param $entity_id
     * @return JsonResponse
     */
    public function likesUsers($entity_id)
    {
        $entity = $this->entityRepo->read($entity_id);

        $usersArr = [];
        $likes = $entity->likes;

        foreach ($likes as $like) {
            array_push($usersArr, $like->user);
        }

        return Response::dataResponse(true, ['users' => $usersArr]);
    }


}