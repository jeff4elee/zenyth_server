<?php

namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Repositories\PinpostRepository;
use App\Repositories\TagRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    private $tagRepo;
    private $pinpostRepo;

    function __construct(TagRepository $tagRepo, PinpostRepository $pinpostRepo)
    {
        $this->tagRepo = $tagRepo;
        $this->pinpostRepo = $pinpostRepo;
    }

    /**
     * Return tags sorted in descending order based on how common the tag's
     * names are
     * @param Request $request
     * @return JsonResponse
     */
    public function searchTags(Request $request)
    {
        $tagName = $request->input('tag');
        $tags = $this->tagRepo
            ->tagsWithSimilarNames($tagName)
            ->joinTaggables()->groupByTagsName()->orderByCount()
            ->paginate(10);

        return Response::dataResponse(true, [
            'tags' => $tags->all()
        ]);
    }

    /**
     * Get all entities associated with this tag
     * @param Request $request
     * @return JsonResponse
     */
    public function getTagInfo(Request $request)
    {
        $tagName = $request->input('tag');
        $user = $request->get('user');

        $tag = $this->tagRepo->findBy('name', $tagName);

        // Get latest pinposts of this tag using eloquent polymorphic
        // relationship
        $pinposts = $tag->pinposts()->latest()->get();
        $pinposts = $this->pinpostRepo->filterByPrivacy($user, $pinposts);

        return Response::dataResponse(true, [
            'pinposts' => $pinposts
        ]);
    }
}