<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use Illuminate\Http\Request;
use App\Pinpost;
use App\Tag;
use App\PinpostTag;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    /**
     * Return tags sorted in descending order based on how common the tag's
     * names are
     * @param Request $request
     * @return $this
     */
    public function searchTags(Request $request)
    {
        $tagName = $request->input('tag');
        $tags = Tag::select('tags.*', DB::raw('count(pinpost_tags.id)'))
            ->where('tags.tag', 'like', '%'.$tagName.'%')
            ->join('pinpost_tags', 'pinpost_tags.tag_id', '=', 'tags.id')
            ->groupBy('tags.tag')->orderBy(DB::raw('count(pinpost_tags.id)'),
                'desc')
            ->paginate(10); // display only the first 10 results

        return Response::dataResponse(true, [
            'tags' => $tags->all()
        ]);
    }

    /**
     * Get all entities associated with this tag
     * @param Request $request
     */
    public function getTagInfo(Request $request)
    {
        $tagName = $request->input('tag');
        $tag = Tag::where('tag', '=', $tagName)->first();
        if($tag) {
            // Get all pinpost tags of this tag
            $pinpostTags = $tag->pinpostTags;
            $pinposts = array();

            // Push all the pinposts of the pinpostTags onto the array
            foreach($pinpostTags as $pinpostTag) {
                array_push($pinposts, $pinpostTag->pinpost);
            }
            return Response::dataResponse(true, [
                'pinposts' => $pinposts
            ]);
        }

        return Response::dataResponse(true, [
            'pinposts' => []
        ]);
    }
}