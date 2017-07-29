<?php

namespace App\Repositories;


use App\Exceptions\Exceptions;

class EntitysPictureRepository extends Repository
{
    function model()
    {
        return 'App\EntitysPicture';
    }

    public function create($request)
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
            Exceptions::unknownErrorException(OBJECT_FAIL_TO_CREATE);
    }

}