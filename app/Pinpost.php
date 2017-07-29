<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
        'thumbnail_id', 'updated_at', 'entity_id', 'creator_id'];

    protected $hidden = ['thumbnail_id', 'creator_id'];
    protected $table = 'pinposts';

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function images() {
        return $this->morphMany('App\Image', 'imageable');
    }

    public function comments() {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function likes() {
        return $this->morphMany('App\Like', 'likeable');
    }

    public function tags() {
        return $this->morphToMany('App\Tag', 'taggable');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $response['creator'] = $this->creator;
        $response['tags'] = $this->tags;
        $response['images'] = $this->images->makeHidden([
            'id', 'imageable_id', 'imageable_type', 'user_id'
        ]);
        $response['likes'] = $this->likes->makeHidden([
            'id', ''
        ]);
        $response['comments'] = $this->comments;
        return $response;
    }
}
