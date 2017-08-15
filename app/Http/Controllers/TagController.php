<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
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
            ->simplePaginate(10)->toArray();

        $tags['tags'] = $tags['data'];
        unset($tags['data']);
        $nextPageUrl = $tags['next_page_url'];
        $prevPageUrl = $tags['prev_page_url'];

        // Add back the search query keyword to the url
        if ($nextPageUrl)
            $tags['next_page_url'] = $nextPageUrl . '&tag=' . $tagName;
        if ($prevPageUrl)
            $tags['prev_page_url'] = $prevPageUrl . '&tag=' . $tagName;

        return Response::dataResponse(true, $tags);
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
        if ($tag == null)
            Exceptions::notFoundException(sprintf(OBJECT_NOT_FOUND, TAG));

        // Get latest pinposts of this tag using eloquent polymorphic
        // relationship
        $pinposts = $tag->pinposts()->latest()->get();
        $pinposts = $this->pinpostRepo->filterByPrivacy($user, $pinposts);

        return Response::dataResponse(true, [
            'pinposts' => $pinposts
        ]);
    }
}