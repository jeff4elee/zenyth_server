<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use App\Exceptions\RepositoryException;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class Repository
 * @package App\Repositories
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * @var App
     */
    private $app;

    /**
     * Model specific to this repository
     * @var Model
     */
    protected $model;

    /**
     * Repository constructor.
     * @param App $app
     * @param Collection $collection
     */
    public function __construct(App $app, Collection $collection) {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all(array $fields = ['*'])
    {
        $data = $this->model->get($fields);

        // Reset the model before we return the data
        $this->makeModel();
        return $data;
    }

    /**
     * Create a model's object
     * @param $request
     * @return mixed
     */
    public function create($request)
    {
        $filteredData = $this->filterData($request);

        return $this->model->create($filteredData);
    }

    /**
     * Update a model's object
     * @param $request
     * @param $model, id|eloquent model
     * @param $attribute
     * @return mixed
     */
    public function update($request, $model = null, $attribute = 'id')
    {
        $filteredData = $this->filterData($request);

        if($model instanceof Model)
            return $model->update($filteredData);
        else if($model != null)
            return $this->model->where($attribute, '=', $model)->update
            ($filteredData);
        else
            Exceptions::invalidParameterException(EITHER_MODEL_OR_ID);
    }

    /**
     * Delete a model's object
     * @param $model, id|eloquent model
     * @return mixed
     */
    public function delete($model = null)
    {
        if($model instanceof Model)
            return $model->delete();
        else if($model != null) {
            $model = $this->model->find($model);
            return $model->delete();
        }
        else
            Exceptions::invalidParameterException(EITHER_MODEL_OR_ID);
    }

    /**
     * Read data from a model's object
     * @param $id
     * @param array $fields
     * @return mixed
     */
    public function read($id, $fields = ['*'])
    {
        $columns = $this->model->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->model->getTable());

        // Filter out the invalid fields if fields are provided
        if (!in_array('*', $fields)) {
            foreach ($fields as $field) {
                if (!in_array($field, $columns)) {
                    $fields = array_diff($fields, [$field]);
                }
            }
        }
        if(count($fields) == 0)
            Exceptions::invalidColumnException();

        return $this->model->find($id, $fields);

    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $count
     * @return mixed
     */
    public function paginate($count)
    {
        $query = $this->model->paginate($count);
        $this->model = $query;
        return $query;
    }

    /**
     * Get the latest objects
     * @return mixed
     */
    public function latest()
    {
        $query = $this->model->latest();
        $this->model = $query;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function select($fields = ['*'])
    {
        $query = $this->model->select($fields);
        $this->model = $query;
        return $this;
    }

    /**
     * Get distinct elements
     * @return $this
     */
    public function distinct()
    {
        $query = $this->model->distinct();
        $this->model = $query;
        return $this;
    }

    /**
     * @param $queryOne
     * @param $queryTwo
     * @return $this
     */
    public function union($queryOne, $queryTwo)
    {
        $query = $queryOne->union($queryTwo);
        $this->model = $query;
        return $this;
    }

    /**
     * Specify the Model class name
     * @return mixed
     */
    abstract function model();

    /**
     * Save the model specific to this repository
     * @return Model|mixed
     * @throws RepositoryException
     */
    public function makeModel()
    {
        // Saves the abstract model defined by children' classes into the
        // $model object
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
            Exceptions::repositoryException('{$this->model()} must be an '.
            'instance of Illuminate\Database\Eloquent\Model');

        return $this->model = $model;
    }

    /**
     * Get the query object
     * @return Model
     */
    public function getQuery()
    {
        return $this->model;
    }

    /**
     * Reset the query
     * @return $this
     */
    public function resetQuery()
    {
        $this->makeModel();
        return $this;
    }

    public function filterData($request)
    {
        $columns = $this->model->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->model->getTable());

        if($request instanceof Request)
            $data = $request->all();
        else
            $data = $request;

        // Filter out the keys in the request that aren't part of the
        // columns
        $filteredData = array_filter($data, function($field) use ($columns) {
            return in_array($field, $columns);
        }, ARRAY_FILTER_USE_KEY);

        return $filteredData;
    }

}