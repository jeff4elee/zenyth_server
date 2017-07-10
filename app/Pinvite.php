<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pinvite extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude',
        'thumbnail_id', 'event_time'];
    protected $table = 'pinvites';

    public function entity()
    {
        return $this->belongsTo('App\Entity', 'entity_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function invitations()
    {
        return $this->hasMany('App\Invitation', 'pinvite_id');
    }

    public function invitees()
    {
        $invitees_arr = [];
        $invitations = $this->invitations;
        foreach ($invitations as $invitation) {
            array_push($invitees_arr, $invitation->invitee);
        }
        return $invitees_arr;
    }

    public function thumbnail()
    {
        return $this->belongsTo('App\Image', 'thumbnail_id');
    }

}
