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
        $query = $this->model->where('tag', '=', $tagName);
        $this->model = $query;
        return $this;
    }

    /**
     * Group by tags name
     * @return mixed
     */
    public function groupByTagsName()
    {
        $query = $this->model->groupBy('tags.name');
        $this->model = $query;
        return $this;
    }

}