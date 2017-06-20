<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commentable_entity extends Model
{
    protected $fillable = ['comment', 'entity_id', 'user_id'];

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }
}
