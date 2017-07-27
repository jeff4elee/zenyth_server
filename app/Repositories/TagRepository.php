<?php

namespace App\Repositories;

use Illuminate\Http\Request;

class TagRepository extends Repository
{
    function model()
    {
        return 'App\Tag';
    }

    public function create(Request $request)
    {
        return $this->model->create([
            'tag' => $request->get('tag')
        ]);
    }
}