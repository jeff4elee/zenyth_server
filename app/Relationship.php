<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'relation_type', 'status'];
}
