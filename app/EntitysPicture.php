<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntitysPicture extends Model
{
    public $timestamps = false;
    protected $table = 'entitys_pictures';

    public function entity()
    {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function image()
    {
        return $this->belongsTo('App\Image', 'image_id');
    }
}