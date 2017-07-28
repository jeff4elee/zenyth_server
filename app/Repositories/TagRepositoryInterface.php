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
     * @return mixed
     */
    public function joinPinpostThroughPinpostTags();

    /**
     * Join with pinpost tags table
     * @return mixed
     */
    public function joinPinpostTags();

    /**
     * Group by tags name
     * @return mixed
     */
    public function groupByTagsName();

    /**
     * Order by how many tags there are
     * @return mixed
     */
    public function orderByCount($option = 'desc');

}