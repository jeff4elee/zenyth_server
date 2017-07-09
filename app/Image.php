<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    protected $fillable = ['filename'];

    protected $table = 'images';
    public $timestamps = false;

}
