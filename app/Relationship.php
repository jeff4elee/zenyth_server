<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'relation_type', 'status'];

    public function userOne() {
        return $this->belongsTo('App\User', 'user_one_id');
    }

    public function userTwo() {
        return $this->belongsTo('App\User', 'user_two_id');
    }

    public function relation_type() {
        return $this->belongsTo('App\Relation', 'relation_type');
    }
}
