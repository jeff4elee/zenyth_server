<?php

namespace App\Http\Requests;

class DataRules
{

    static public function registerRules()
    {
        return [
            'first_name' => 'required|alpha',
            'last_name' => 'required|alpha',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|AlphaNum|min:8|max:16|confirmed',
            'gender' => 'required'
        ];
    }

    static public function loginRules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required'
        ];
    }

    static public function pinpostRules()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'thumbnail' => 'image'
        ];
    }

    static public function pinviteRules()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'thumbnail' => 'image',
            'event_time' => 'required'
        ];
    }

    static public function pictureRules()
    {
        return ['file' => 'image'];
    }

    static public function likeRules()
    {
        return ['entity_id' => 'required'];
    }

    static public function commentRules()
    {
        return [
            'entity_id' => 'required',
            'comment' => 'required|min:1',
            'on_entity_id' => 'required',
        ];
    }

    static public function friendRequestRules()
    {
        return [
            'requestee_id' => 'required|exists:users,id'
        ];
    }

}