<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{

    public $timestamps = false;

    public function entity()
    {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

}
