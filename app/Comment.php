<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['comment', 'user_id', 'commentable_id',
        'commentable_type'];
    protected $table = 'comments';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function images()
    {
        return $this->morphMany('App\Image', 'imageable');
    }

    public function commentable()
    {
        return $this->morphTo();
    }
}
