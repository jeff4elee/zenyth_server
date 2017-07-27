<?php

namespace App\Repositories;


use Illuminate\Http\Request;

class PinpostTagRepository extends Repository
{
    function model()
    {
        return 'App\PinpostTag';
    }

    public function create(Request $request)
    {
        $pinpost = $request->get('pinpost');
        $tag = $request->get('tag');
        return $this->model->create([
            'tag_id' => $tag->id,
            'pinpost_id' => $pinpost->id
        ]);
    }
}