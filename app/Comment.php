<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['comment', 'user_id', 'commentable_id',
        'commentable_type'];
    protected $hidden = ['commentable_id', 'commentable_type', 'id', 'user_id'];
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

    public function likes()
    {
        return $this->morphMany('App\Like', 'likeable');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $response['user'] = $this->user;
        $response['likes'] = $this->likes;
        $response['images'] = $this->images;
        return $response;
    }
}
