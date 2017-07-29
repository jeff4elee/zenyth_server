<?php

namespace App\Repositories;

interface RepositoryInterface
{
    public function all(array $fields = ['*']);

    public function create($request);

    public function update($request, $id);

    public function delete($request, $id);

    public function read($id, $fields = ['*']);

    public function findBy($attribute, $value, $columns = ['*']);

    public function remove($model);
}