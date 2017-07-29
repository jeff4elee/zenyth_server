<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Repositories\EntityRepository;
use App\Repositories\ImageableRepository;
use App\Repositories\ImageRepository;
use App\Repositories\PinpostRepository;
use App\Repositories\TaggableRepository;
use App\Repositories\TagRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PinpostController
 * @package App\Http\Controllers
 */
class PinpostController extends Controller
{
    use AuthenticationTrait;

    private $pinpostRepo;
    private $entityRepo;
    private $imageRepo;
    private $taggableRepo;
    private $tagRepo;

    public function __construct(PinpostRepository $pinpostRepo,
                                EntityRepository $entityRepo,
                                ImageRepository $imageRepo,
                                TaggableRepository $taggableRepo,
                                TagRepository $tagRepo)
    {
        $this->pinpostRepo = $pinpostRepo;
        $this->entityRepo = $entityRepo;
        $this->imageRepo = $imageRepo;
        $this->taggableRepo = $taggableRepo;
        $this->tagRepo = $tagRepo;
    }

    /**
     * Create a Pinpost, storing thumbnail image if there is any
     * @param Request $request, post request
     *        rules: requires title, description, latitude,
     *          longitude, event_time
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $entity = $this->entityRepo->create(new Request());
        // Inject this entity into the request for the pinpost repository to
        // use
        $request->merge(['entity' => $entity]);
        $pin = $this->pinpostRepo->create($request);

        if($request->has('tags')) {
            // Tag must be in the form "tag1,tag2,tag3"
            // Must parse the hash tags out on client side
            $tags = strtolower($request->input('tags'));
            $tags = explode(",", $tags);
            $request->merge(['pinpost' => $pin]);
            foreach($tags as $tagName) {
                $tag = $this->tagRepo->findBy('name', $tagName);

                // If tag already exists, create another PinpostTag that
                // associates with this pinpost and the tag
                if($tag) {
                    $data = [
                        'taggable_type' => 'App\Pinpost',
                        'tag_id' => $tag->id,
                        'taggable_id' => $pin->id
                    ];
                    $this->taggableRepo->create($data);
                }
                // If tag does not exist, create one
                else {
                    $data = [
                        'name' => $tagName
                    ];
                    $tag = $this->tagRepo->create($data);
                    $data = [
                        'tag_id' => $tag->id,
                        'taggable_type' => 'App\Pinpost',
                        'taggable_id' => $pin->id
                    ];
                    $this->taggableRepo->create($data);
                }
            }
        }
        return Response::dataResponse(true, ['pinpost' => $pin]);
    }

    /**
     * Give back information on Pinpost
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function read(Request $request, $pinpost_id)
    {
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
            $pin = $this->pinpostRepo->read($pinpost_id, $fields);
        }
        else
            $pin = $this->pinpostRepo->read($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException(NOT_FOUND);

        return Response::dataResponse(true, ['pinpost' => $pin]);
    }

    /**
     * Update Pinpost with information
     * @param Request $request, post request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function update(Request $request, $pinpost_id)
    {
        $pin = $this->pinpostRepo->update($request, $pinpost_id);
        return Response::dataResponse(true, ['pinpost' => $pin]);
    }

    /**
     * Delete the pinpost
     * @param Request $request, delete request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function delete(Request $request, $pinpost_id)
    {
        // Check if pinpost is there
        $pin = $this->pinpostRepo->read($pinpost_id);
        if ($pin == null)
            Exceptions::notFoundException(NOT_FOUND);

        /* Validate if user deleting is the same as the user from the token */
        $api_token = $pin->creator->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $entitysPictures = $pin->entity->pictures;

        $request->merge(['directory' => 'images']);
        foreach($entitysPictures as $entitysPicture) {
            $this->imageRepo->delete($request, $entitysPicture->image_id);
        }
        $this->entityRepo->delete($request, $pin->entity_id);

        return Response::successResponse(DELETE_SUCCESS);
    }

    public function uploadImage(Request $request, $pinpost_id)
    {
        $user = $request->get('user');
        $image = $request->file('image');
        $request->merge([
            'user_id' => $user->id,
            'image_file' => $image,
            'directory' => 'images',
            'imageable_id' => $pinpost_id,
            'imageable_type' => 'App\Pinpost'
        ]);
        $this->imageRepo->create($request);
        return Response::successResponse(UPLOAD_SUCCESS);
    }

    /**
     * Fetch all pinposts of friends ordered by latest first
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request)
    {
        $type = strtolower($request->input('type'));
        $user = $request->get('user');
        $scope = $request->input('scope');

        if($type == 'radius')
            $this->pinpostRepo->pinpostsInRadius($request->all());
        else
            $this->pinpostRepo->pinpostsInFrame($request->all());

        $this->pinpostRepo->pinpostsWithScope($scope, $user);
        $this->pinpostRepo->latest();

        // FriendsScope is either not provided or public. Return all pinposts in the
        // area

        return Response::dataResponse(true, [
            'pinposts' => $this->pinpostRepo->all() // get all the pinposts
        ]);
    }

}
