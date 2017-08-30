<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
        'updated_at', 'user_id', 'privacy'];

    protected $visible = ['title', 'description', 'latitude', 'longitude',
        'updated_at', 'user_id', 'privacy', 'creator'];
    protected $table = 'pinposts';

    protected static function boot()
    {
        parent::boot();
        Pinpost::deleting(function($pinpost) {
            foreach($pinpost->comments as $comment)
                $comment->delete();

            foreach($pinpost->images as $image)
                $image->delete();

            foreach($pinpost->likes as $like)
                $like->delete();
        });
    }

    public function creator() {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function images() {
        return $this->morphMany('App\Image', 'imageable');
    }

    public function comments() {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function commentsCount() {
        return $this->comments()->count();
    }

    public function likes() {
        return $this->morphMany('App\Like', 'likeable');
    }

    public function likesCount() {
        return $this->likes()->count();
    }

    public function tags() {
        return $this->morphToMany('App\Tag', 'taggable');
    }

    public function toArray()
    {
        // Eager loads to show the creator's full credential
        if(!in_array('creator', $this->hidden))
            $this->creator;

        $response = parent::toArray();

        if(!in_array('comments_count', $this->hidden))
            $response['comments_count'] = $this->commentsCount();

        if(in_array('comments', $this->visible))
            $response['comments'] = $this->comments;

        if(!in_array('likes_count', $this->hidden))
            $response['likes_count'] = $this->likesCount();

        if(in_array('likes', $this->visible))
            $response['likes'] = $this->likes;

        if(!in_array('images', $this->hidden))
            $response['images'] = $this->images;

        return $response;
    }
}
