<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taggable extends Model
{
    protected $table = 'taggables';
    protected $fillable = ['taggable_id', 'taggable_type', 'tag_id'];
    public $timestamps = false;

}