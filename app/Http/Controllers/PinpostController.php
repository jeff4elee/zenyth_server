<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Repositories\Criteria\Pinpost\FrameCriteria;
use App\Repositories\Criteria\Pinpost\FriendsScope;
use App\Repositories\Criteria\Pinpost\LatestPinpost;
use App\Repositories\Criteria\Pinpost\RadiusCriteria;
use App\Repositories\Criteria\Pinpost\SelfScope;
use App\Repositories\EntityRepository;
use App\Repositories\EntitysPictureRepository;
use App\Repositories\ImageRepository;
use App\Repositories\PinpostRepository as PinpostRepo;
use App\Repositories\PinpostTagRepository;
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
    private $entitysPictureRepo;
    private $imageRepo;
    private $pinpostTagRepo;
    private $tagRepo;

    public function __construct(PinpostRepo $pinpostRepo,
                                EntityRepository $entityRepo,
                                EntitysPictureRepository $entitysPictureRepo,
                                ImageRepository $imageRepo,
                                PinpostTagRepository $pinpostTagRepo,
                                TagRepository $tagRepo)
    {
        $this->pinpostRepo = $pinpostRepo;
        $this->entityRepo = $entityRepo;
        $this->entitysPictureRepo = $entitysPictureRepo;
        $this->imageRepo = $imageRepo;
        $this->pinpostTagRepo = $pinpostTagRepo;
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

        if($file = $request->file('thumbnail')) {
            // Inject image file into request so we can create the image in
            // ImageRepository
            $request->merge(['image_file' => $file]);

            $image = $this->imageRepo->create($request);

            // Inject image object and entity object into request so we can
            // create the entitys picture that associates with this image
            $request->merge([
                'image' => $image,
                'entity' => $entity
            ]);
            $this->entitysPictureRepo->create($request);
            $pin->thumbnail_id = $image->id;
            $pin->update();
        }

        if($request->has('tags')) {
            // Tag must be in the form "tag1,tag2,tag3"
            // Must parse the hash tags out on client side
            $tags = strtolower($request->input('tags'));
            $tags = explode(",", $tags);
            $request->merge(['pinpost' => $pin]);
            foreach($tags as $tagName) {
                $tag = $this->tagRepo->findBy('tag', $tagName);

                // If tag already exists, create another PinpostTag that
                // associates with this pinpost and the tag
                if($tag) {
                    $request->merge(['tag' => $tag]);
                    $this->pinpostTagRepo->create($request);
                }
                // If tag does not exist, create one
                else {
                    $request->merge(['tag' => $tagName]);
                    $tag = $this->tagRepo->create($request);
                    $request->merge(['tag' => $tag]);
                    $this->pinpostTagRepo->create($request);
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

        if($file = $request->file('thumbnail')) {
            // Inject image file into request so we can create the image in
            // ImageRepository
            $request->merge(['image_file' => $file]);
            $this->imageRepo->update($request, $pin->thumbnail_id);
        }

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
            Exceptions::invalidTokenException('Pinpost does not associate with this token');

        $entitysPictures = $pin->entity->pictures;

        $request->merge(['directory' => 'images']);
        foreach($entitysPictures as $entitysPicture) {
            $this->imageRepo->delete($request, $entitysPicture->image_id);
        }
        $this->entityRepo->delete($request, $pin->entity_id);

        return Response::successResponse();
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
            $this->pinpostRepo->pushCriteria(
                new RadiusCriteria($request->all()));
        else
            $this->pinpostRepo->pushCriteria(new FrameCriteria($request->all()));

        if($scope == "self")
            $this->pinpostRepo->pushCriteria(new SelfScope($user));
        else if($scope == "friends")
            $this->pinpostRepo->pushCriteria(new FriendsScope($user));

        $this->pinpostRepo->pushCriteria(new LatestPinpost());

        // FriendsScope is either not provided or public. Return all pinposts in the
        // area
        return Response::dataResponse(true, [
            'pinposts' => $this->pinpostRepo->all() // get all the pinposts
        ]);
    }

}
