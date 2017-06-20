<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    public function relationship() {
        return $this->hasMany('App\Relationship', 'relation_type');
    }
}
