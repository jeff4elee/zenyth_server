<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'entity_id'];
    public $timestamps = false;
    protected $table = 'likes';

    public function entity()
    {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

}
