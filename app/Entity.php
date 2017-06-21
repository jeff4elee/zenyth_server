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
        return $this->hasMany('App\Like', 'entity_id');
    }

    public function comment() {
        return $this->hasMany('App\Comment', 'entity_id');
    }
}
