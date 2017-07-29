<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    protected $fillable = ['filename', 'imageable_id', 'imageable_type',
        'directory', 'user_id'];
    protected $hidden = ['directory', 'imageable_type', 'imageable_id'];
    protected $table = 'images';
    public $timestamps = false;

    public function imageable()
    {
        return $this->morphTo();
    }

}
