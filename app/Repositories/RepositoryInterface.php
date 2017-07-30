<?php

namespace App\Repositories;

interface RepositoryInterface
{
    public function all(array $fields = ['*']);

    public function create($request);

    public function update($request, $model, $attribute = 'id');

    public function delete($model);

    public function read($id, $fields = ['*']);

    public function findBy($attribute, $value, $columns = ['*']);
}