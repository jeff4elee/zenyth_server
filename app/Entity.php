<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    public function pinvite() {
        return $this->hasMany('App\Pinvite', 'entity_id');
    }

    public function pinpost() {
        return $this->hasMany('App\Pinpost', 'entity_id');
    }

    public function likable_entity() {
        return $this->hasMany('App\Likable_entity', 'entity_id');
    }

    public function commentable_entity() {
        return $this->hasMany('App\Commentable_entity', 'entity_id');
    }
}
