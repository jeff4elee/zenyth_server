<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['tag'];
    protected $table = 'tags';
    public $timestamps = false;

    public function pinpostTags() {
        return $this->hasMany('App\PinpostTag', 'tag_id');
    }

}
