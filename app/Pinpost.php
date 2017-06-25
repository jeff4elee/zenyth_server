<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
                            'thumbnail', 'updated_on'];

    protected $table = 'pinposts';
    public $timestamps = false;

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function thumbnail() {
        return $this->belongsTo('App\Image', 'thumbnail_id');
    }
}
