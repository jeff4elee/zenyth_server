<?php

namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Repositories\TagRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    private $tagRepo;

    function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
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

        $tag = $this->tagRepo->findBy('name', $tagName);

        // Get latest pinposts of this tag using eloquent polymorphic
        // relationship
        $query = $tag->pinposts()->latest()->get();

        return Response::dataResponse(true, [
            'pinposts' => $query
        ]);
    }
}