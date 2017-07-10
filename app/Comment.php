<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['comment'];
    protected $table = 'comments';
    public $timestamps = false;

    public function entity()
    {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function on_entity()
    {
        return $this->belongsTo('App\Entity', 'on_entity_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
