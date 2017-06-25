<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['filename', 'path'];

    protected $table = 'images';
    protected $timestamps = false;
}
