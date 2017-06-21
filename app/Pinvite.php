<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinvite extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
                            'thumbnail', 'event_time'];

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
}
