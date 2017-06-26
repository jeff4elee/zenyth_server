<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{

    public $timestamps = false;
    protected $fillable = ['requester', 'requestee', 'status'];

    public function getRequester() {
        return $this->belongsTo('App\User', 'requester');
    }

    public function getRequestee() {
        return $this->belongsTo('App\User', 'requestee');
    }

}