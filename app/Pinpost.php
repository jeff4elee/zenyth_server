<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
        'thumbnail_id', 'updated_at', 'entity_id', 'creator_id'];

    protected $hidden = ['thumbnail_id', 'creator_id'];
    protected $table = 'pinposts';

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function images() {
        return $this->morphMany('App\Image', 'imageable');
    }

    public function tags() {
        return $this->morphToMany('App\Tag', 'taggable');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $response['creator'] = $this->creator;
        $response['tags'] = $this->tags;
        return $response;
    }
}
