<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'likeable_id', 'likeable_type'];
    protected $hidden = ['id', 'likeable_id', 'likeable_type', 'user_id'];
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
        $response['user'] = $this->user;
        return $response;
    }

}
