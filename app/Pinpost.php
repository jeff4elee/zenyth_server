<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinpost extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
                            'thumbnail', 'updated_on'];

    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
}
