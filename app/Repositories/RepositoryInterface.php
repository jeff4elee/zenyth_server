<?php

namespace App\Repositories;

use Illuminate\Http\Request;

interface RepositoryInterface
{
    public function all(array $fields = ['*']);

    public function create(Request $data);

    public function update(Request $data, $id);

    public function delete($id);

    public function read($id, $fields = ['*']);
}