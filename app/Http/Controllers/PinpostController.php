<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Repositories\CommentRepository;
use App\Repositories\LikeRepository;
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

    /**
     * @var PinpostRepository
     */
    private $pinpostRepo;
    private $commentRepo;
    private $likeRepo;
    private $taggableRepo;
    private $tagRepo;

    public function __construct(PinpostRepository $pinpostRepo,
                                CommentRepository $commentRepo,
                                LikeRepository $likeRepo,
                                TaggableRepository $taggableRepo,
                                TagRepository $tagRepo)
    {
        $this->pinpostRepo = $pinpostRepo;
        $this->commentRepo = $commentRepo;
        $this->likeRepo = $likeRepo;
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
        $user = $request->get('user');
        $data = [
            'user_id' => $user->id,
            'title' => $request['title'],
            'description' => $request['description'],
            'latitude' => (double)$request['latitude'],
            'longitude' => (double)$request['longitude'],
        ];
        if($request->has('privacy'))
            $data = array_add($data, 'privacy', strtolower($request['privacy']));

        $pin = $this->pinpostRepo->create($data);

        if($request->has('tags')) {
            // Tag must be in the form "tag1,tag2,tag3"
            // Must parse the hash tags out on client side
            $tags = strtolower($request->input('tags'));
            $tags = explode(",", $tags);

            // Loop through the tags to generate tags
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

                    // Create a taggable object that associates with this
                    // pinpost
                    $this->taggableRepo->create($data);
                }
                // If tag does not exist, create one
                else {
                    $data = [
                        'name' => $tagName
                    ];
                    $tag = $this->tagRepo->create($data);

                    // Create a taggable object that associates with this
                    // pinpost
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
     * @param $request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function read(Request $request, $pinpost_id)
    {
        // Specify fields to return
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
            $pin = $this->pinpostRepo->read($pinpost_id, $fields);
        }
        else
            $pin = $this->pinpostRepo->read($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException(NOT_FOUND);

        return Response::dataResponse(true, [
            'pinpost' => $pin
        ]);
    }

    /**
     * Get all image objects of this pinpost
     * @param Request $request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function readImages(Request $request, $pinpost_id)
    {
        $pin = $this->pinpostRepo->read($pinpost_id);
        $images = $pin->images;

        return Response::dataResponse(true, [
            'pinpost' => [
                'images' => $images
            ]
        ]);
    }

    /**
     * Update Pinpost with information
     * @param Request $request, post request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function update(Request $request, $pinpost_id)
    {
        $pin = $this->pinpostRepo->read($pinpost_id);

        if (!$pin)
            Exceptions::notFoundException(NOT_FOUND);

        // Validator pinpost's creator
        $pinpostOwnerId = $pin->user_id;
        $userId = $request->get('user')->id;
        if ($userId != $pinpostOwnerId)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $request->except(['user_id']);
        $this->pinpostRepo->update($request, $pin);

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
        $pin = $this->pinpostRepo->read($pinpost_id);

        // Validate pinpost creator
        $pinpostOwnerId = $pin->user_id;
        $userId = $request->get('user')->id;
        if ($userId != $pinpostOwnerId)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $this->pinpostRepo->delete($pin);

        return Response::successResponse(DELETE_SUCCESS);
    }


    /**
     * Fetch all comments of this pinpost
     * @param Request $request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function fetchComments(Request $request, $pinpost_id)
    {
        // Specify fields of comments to return
        $pin = $this->pinpostRepo->read($pinpost_id);
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
        } else
            $fields = ['*'];

        return Response::dataResponse(true, [
            'comments' => $pin->comments()->get($fields)
        ]);
    }

    /**
     * Fetch all likes of this pinpost
     * @param Request $request
     * @param $pinpost_id
     * @return JsonResponse
     */
    public function fetchLikes(Request $request, $pinpost_id)
    {
        $pin = $this->pinpostRepo->read($pinpost_id);

        // Specify fields of likes to return
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
        } else
            $fields = ['*'];

        return Response::dataResponse(true, [
            'comments' => $pin->likes()->get($fields)
        ]);
    }

    /**
     * Fetch all pinposts of friends ordered by latest first
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request)
    {
        // Fetch based on scope
        // Example GET request: /api/pinpost/fetch?type=radius&center=lat,long&radius=100&unit=mi|km&scope=self|friends|public
        // Example GET request: /api/pinpost/fetch?type=frame&top_left=lat,long&bottom_right=lat,long&unit=mi|km&scope=self|friends|public
        $type = strtolower($request->input('type'));
        $user = $request->get('user');
        $scope = $request->input('scope');

        if(!$request->has('unit'))
            $request->merge(['unit' => 'mi']);

        if($type == 'radius')
            $this->pinpostRepo->pinpostsInRadius($request->all());
        else
            $this->pinpostRepo->pinpostsInFrame($request->all());

        $this->pinpostRepo->pinpostsWithScope($scope, $user);
        $this->pinpostRepo->latest();

        // FriendsScope is either not provided or public. Return all pinposts in the
        // area
        $pinposts = $this->pinpostRepo->all();
        $pinposts = $this->pinpostRepo->filterByPrivacy($user, $pinposts);
        return Response::dataResponse(true, [
            'pinposts' => $pinposts // get all the pinposts
        ]);
    }

}
