<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinvite_picture extends Model
{
    public $timestamps = false;

    public function pinvite()
    {
        return $this->belongsTo('App\Pinvite', 'pinvite_id');
    }

    public function image()
    {
        return $this->belongsTo('App\Image', 'image_id');
    }
}
