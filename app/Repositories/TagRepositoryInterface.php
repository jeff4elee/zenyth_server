<?php


namespace App\Repositories;

/**
 * Interface TagRepositoryInterface
 * @package App\Repositories
 */
interface TagRepositoryInterface
{
    /**
     * Get all tags with similar names as this tag
     * @param $tagName
     * @return mixed
     */
    public function tagsWithSimilarNames($tagName);

    /**
     * Get the tag with this name
     * @param $tagName
     * @return mixed
     */
    public function tagWithExactName($tagName);

    /**
     * Group by tags name
     * @return mixed
     */
    public function groupByTagsName();

}