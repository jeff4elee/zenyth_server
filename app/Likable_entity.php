<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Likable_entity extends Model
{
    protected $fillable = ['entity_id', 'user_id'];

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
}
