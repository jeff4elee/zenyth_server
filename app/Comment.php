<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['comment'];

    public $timestamps = false;

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }
}
