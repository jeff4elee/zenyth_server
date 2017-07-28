<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagRepository extends Repository
                    implements TagRepositoryInterface
{
    function model()
    {
        return 'App\Tag';
    }

    public function create(Request $request)
    {
        return $this->model->create([
            'tag' => $request->get('tag')
        ]);
    }

    /**
     * Get all tags with similar names as this tag
     * @param $tagName
     * @return mixed
     */
    public function tagsWithSimilarNames($tagName)
    {
        $query = $this->model->select('tags.*')
            ->where('tags.tag', 'like', '%'.$tagName.'%');

        $this->model = $query;
        return $this;
    }

    /**
     * Get the tag with this name
     * @param $tagName
     * @return mixed
     */
    public function tagWithExactName($tagName)
    {
        $query = $this->model->where('tag', '=', $tagName);
        $this->model = $query;
        return $this;
    }

    /**
     * @return mixed
     */
    public function joinPinpostThroughPinpostTags()
    {
        $query = $this->model
            ->join('pinpost_tags', 'pinpost_tags.tag_id', '=', 'tags.id')
            ->join('pinposts', 'pinpost_tags.pinpost_id', '=', 'pinposts.id');

        $this->model = $query;
        return $this;
    }

    /**
     * Join with pinpost tags table
     * @return mixed
     */
    public function joinPinpostTags()
    {
        $query = $this->model->join('pinpost_tags',
            'pinpost_tags.tag_id', '=', 'tags.id');

        $this->model = $query;
        return $this;
    }

    /**
     * Group by tags name
     * @return mixed
     */
    public function groupByTagsName()
    {
        $query = $this->model->groupBy('tags.tag');
        $this->model = $query;
        return $this;
    }

    /**
     * Order by how many tags there are
     * @param $option
     * @return mixed
     */
    public function orderByCount($option = 'desc')
    {
        $query = $this->model->orderBy
        (DB::raw('count(pinpost_tags.id)'), $option);

        $this->model = $query;
        return $this;
    }

}