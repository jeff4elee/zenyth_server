<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PinpostTag extends Model
{
    protected $table = 'pinpost_tags';
    protected $fillable = ['pinpost_id', 'tag_id'];
    public $timestamps = false;

    public function tag() {
        return $this->belongsTo('App\Tag', 'tag_id');
    }

    public function pinpost() {
        return $this->belongsTo('App\Pinpost', 'pinpost_id');
    }
}
