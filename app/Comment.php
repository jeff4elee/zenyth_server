<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['comment', 'user_id', 'commentable_id',
        'commentable_type'];
    protected $hidden = ['commentable_type'];
    protected $table = 'comments';
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        Comment::deleting(function($comment) {
            foreach($comment->images as $image)
                $image->delete();

            foreach($comment->likes as $like)
                $like->delete();
        });
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function replies()
    {
        return $this->hasMany('App\Reply', 'comment_id');
    }

    public function repliesCount()
    {
        return $this->replies()->count();
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

    public function likesCount()
    {
        return $this->likes()->count();
    }
}
