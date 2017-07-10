<?php

namespace App\Http\Requests;

class DataRules
{

    static public function registerRules()
    {
        return [
            'username' => 'required|min:3|max:20|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|alpha_num|min:8|max:16|confirmed',
            'gender' => 'required'
        ];
    }

    static public function loginRules()
    {
        return [
            'username' => 'required',
            'password' => 'required'
        ];
    }

    static public function sendResetPWEmailRules()
    {
        return [
            'email' => 'required|email'
        ];
    }

    static public function resetPasswordRules()
    {
        return [
            'password' => 'required|alpha_num|confirmed|min:8|max:16'
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
            'comment' => 'required|min:1',
            'on_entity_id' => 'required'
        ];
    }

    static public function friendRequestRules()
    {
        return [
            'requestee_id' => 'required|exists:users,id'
        ];
    }

}