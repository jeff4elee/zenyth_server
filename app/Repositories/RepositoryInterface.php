<?php

namespace App\Repositories;

use Illuminate\Http\Request;

interface RepositoryInterface
{
    public function all(array $fields = ['*']);

    public function create(Request $data);

    public function update(Request $data, $id);

    public function delete(Request $request, $id);

    public function read($id, $fields = ['*']);

    public function findBy($attribute, $value, $columns = ['*']);
}