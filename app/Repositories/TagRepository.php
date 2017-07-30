<?php

namespace App\Repositories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\DB;

class TagRepository extends Repository
                    implements TagRepositoryInterface
{
    function model()
    {
        return 'App\Tag';
    }

    /**
     * Get all tags with similar names as this tag
     * @param $tagName
     * @return mixed
     */
    public function tagsWithSimilarNames($tagName)
    {
        $query = $this->model->select('tags.*')
            ->where('tags.name', 'like', '%'.$tagName.'%');
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
        $query = $this->model->where('name', '=', $tagName);
        $this->model = $query;
        return $this;
    }

    /**
     * Group by tags name
     * @return mixed
     */
    public function groupByTagsName()
    {
        $query = $this->model->groupBy('tags.id');
        $this->model = $query;
        return $this;
    }

    /**
     * Join taggables table
     * @return mixed
     */
    public function joinTaggables()
    {
        $query = $this->model->join('taggables',
            'taggables.tag_id', '=', 'tags.id');
        $this->model = $query;
        return $this;
    }

    /**
     * Order by the number of objects associated with the tag in descending
     * order
     * @return mixed
     */
    public function orderByCount()
    {
        $query = $this->model->orderBy(DB::raw('count(taggables.tag_id)'),
            'desc');
        $this->model = $query;
        return $this;
    }


}