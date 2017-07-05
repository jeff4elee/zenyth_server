<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    public $timestamps = false;
    protected $table = 'entities';

    public function likes()
    {
        return $this->hasMany('App\Like', 'entity_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment', 'entity_id');
    }

    public function pictures()
    {
        return $this->hasMany('App\EntitysPicture', 'entity_id');
    }

    public function commentsCount()
    {
        return $this->comments()->count();
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }
}
