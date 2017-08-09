<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
        'updated_at', 'user_id', 'privacy'];

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
        $response = parent::toArray();
        $response['comments'] = $this->commentsCount();
        $response['likes'] = $this->likesCount();
        $response['images'] = $this->images;
        return $response;
    }
}
