<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'likeable_id', 'likeable_type'];
    public $timestamps = false;
    protected $table = 'likes';

    public function likeable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $likeableType = substr($this->likeable_type, 4);
        $response['likeable_type'] = $likeableType;
        return $response;
    }

}
