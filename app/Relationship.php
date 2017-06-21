<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $fillable = ['requester', 'requestee', 'relation_type', 'status'];

    public function requester() {
        return $this->belongsTo('App\User', 'requester');
    }

    public function requestee() {
        return $this->belongsTo('App\User', 'requestee');
    }

    public function relation_type() {
        return $this->belongsTo('App\Relation', 'relation_type');
    }
}
