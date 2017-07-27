<?php

namespace App\Repositories;


use App\Exceptions\Exceptions;
use Illuminate\Http\Request;

class EntitysPictureRepository extends Repository
{
    function model()
    {
        return 'App\EntitysPicture';
    }

    public function create(Request $request)
    {
        $image = $request->get('image');
        $entity = $request->get('entity');
        $entitysPicture = $this->model->create([
            'image_id' => $image->id,
            'entity_id' => $entity->id
        ]);

        if($entitysPicture)
            return $entitysPicture;
        else
            Exceptions::unknownErrorException('Could not create Entitys Picture');
    }

}