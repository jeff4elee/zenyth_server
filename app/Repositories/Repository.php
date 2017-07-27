<?php

namespace App\Repositories;

use App\Repositories\Criteria\Criteria;
use App\Repositories\Criteria\CriteriaInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use App\Exceptions\Exceptions;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

abstract class Repository implements RepositoryInterface, CriteriaInterface
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
     * @var Collection
     */
    private $criteria;

    /**
     * If true, Repository won't apply the criteria
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Repository constructor.
     * @param App $app
     * @param Collection $collection
     */
    public function __construct(App $app, Collection $collection) {
        $this->app = $app;
        $this->criteria = $collection;
        $this->resetScope();
        $this->makeModel();
    }

    public function all(array $fields = ['*'])
    {
        $this->applyCriteria();
        return $this->model->get($fields);
    }

    /**
     * Create a model's object
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        return $this->model->create($request->all());
    }

    /**
     * Update a model's object
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id, $attribute = 'id')
    {
        return $this->model->where($attribute, '=', $id)->update
        ($request->all());
    }

    /**
     * Delete a model' object
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Read data from a model's object
     * @param $id
     * @param array $fields
     * @return mixed
     */
    public function read($id, $fields = ['*'])
    {
        $this->applyCriteria();
        return $this->model->find($id, $fields);
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
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * Skip applying the criteria
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * Return criteria
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria)
    {
        $this->model = $criteria->apply($this->model);
        return $this;
    }

    /**
     * Push a criteria onto a collection
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria)
    {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * Apply criteria to the model
     * @return $this
     */
    public function applyCriteria()
    {
        // Skip applying the criteria
        if($this->skipCriteria === true)
            return $this;

        // Applying criteria by chaining Criteria on the model
        foreach($this->getCriteria() as $criteria) {
            if($criteria instanceof Criteria)
                $this->model = $criteria->apply($this->model);
        }

        return $this;
    }

}