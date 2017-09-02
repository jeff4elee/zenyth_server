<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['text', 'user_id', 'commentable_id',
        'commentable_type'];
    protected $table = 'comments';
    protected $visible = ['id', 'text', 'commentable_id', 'commentable_type',
        'user_id', 'creator', 'likes_count', 'replies_count'];

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

    public function toArray()
    {
        $response = parent::toArray();
        $commentableType = substr($this->commentable_type, 4);
        $response['commentable_type'] = $commentableType;

        if(!in_array('replies_count', $this->hidden))
            $response['replies_count'] = $this->repliesCount();

        if(in_array('replies', $this->visible))
            $response['replies'] = $this->replies;

        if(!in_array('likes_count', $this->hidden))
            $response['likes_count'] = $this->likesCount();

        if(in_array('likes', $this->visible))
            $response['likes'] = $this->likes;
        $response['images'] = $this->images;

        if(!in_array('creator', $this->hidden))
            $response['creator'] = $this->creator;
        return $response;
    }
}
