<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{

    protected $fillable = ['requester', 'requestee', 'status'];

    public function requester() {
        return $this->belongsTo('App\User', 'requester');
    }

    public function requestee() {
        return $this->belongsTo('App\User', 'requestee');
    }

}