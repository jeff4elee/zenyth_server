<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
        'thumbnail', 'updated_at', 'entity_id', 'creator_id'];

    protected $table = 'pinposts';

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function thumbnail() {
        return $this->belongsTo('App\Image', 'thumbnail_id');
    }

    public function tag() {
        return $this->hasMany('App\Tag', 'pinpost_id');
    }
}
