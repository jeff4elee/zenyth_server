<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{

    public $timestamps = false;
    protected $fillable = ['requester', 'requestee', 'status', 'blocked'];
    protected $table = 'relationships';

    public function getRequester()
    {
        return $this->belongsTo('App\User', 'requester');
    }

    public function getRequestee()
    {
        return $this->belongsTo('App\User', 'requestee');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $response['requester'] = $this->getRequester;
        $response['requestee'] = $this->getRequestee;
        $response['status'] = (bool) $this->status;
        $response['blocked'] = (bool) $this->blocked;
        return $response;
    }

}