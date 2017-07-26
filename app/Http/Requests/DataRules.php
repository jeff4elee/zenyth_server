<?php

namespace App\Http\Requests;

class DataRules
{

    static public function registerRules()
    {
        return [
            'username' => 'required|min:3|max:20|alpha_dash',
            'email' => 'required|email|max:255',
            'password' => 'required|alpha_num|min:8|max:16|confirmed',
            'birthday' => 'date_format:Y-m-d'
        ];
    }

    static public function oauthRegisterRules()
    {
        return [
            'username' => 'required|min:3|max:20',
            'email' => 'required|email|max:255',
            'oauth_type' => 'required'
        ];
    }

    static public function oauthLoginRules()
    {
        return [
            'oauth_type' => 'required',
            'email' => 'required'
        ];
    }

    static public function loginRules()
    {
        return [
            'username' => 'required_without:email',
            'email' => 'email',
            'password' => 'required'
        ];
    }

    static public function sendResetPWEmailRules()
    {
        return [
            'username' => 'required_without:email',
            'email' => 'email'
        ];
    }

    static public function resetPasswordRules()
    {
        return [
            'password' => 'required|alpha_num|confirmed|min:8|max:16'
        ];
    }

    static public function searchUserRules()
    {
        return [
            'keyword' => 'required'
        ];
    }

    static public function createPinpostRules()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'thumbnail' => 'image',
        ];
    }

    static public function updatePinpostRules()
    {
        return [
            'thumbnail' => 'image'
        ];
    }

    static public function fetchPinpostRules()
    {
        return [
            'type' => 'required|in:radius,frame',
            'radius' => 'required_if:type,radius|numeric|min:0',
            'center' => 'required_if:type,radius|valid_coord',
            'first_coord' => 'required_if:type,frame|valid_coord',
            'second_coord' => 'required_if:type,frame|valid_coord',
            'unit' => 'in:km,mi',
            'scope' => 'in:self,friends,public'
        ];
    }

    static public function searchTagRules()
    {
        return [
            'tag' => 'required'
        ];
    }

    static public function createPinviteRules()
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

    static public function createCommentRules()
    {
        return [
            'comment' => 'required|min:1',
            'on_entity_id' => 'required'
        ];
    }

    static public function updateCommentRules()
    {
        return [
            'comment' => 'required|min:1'
        ];
    }

    static public function friendRequestRules()
    {
        return [
            'requestee_id' => 'required|exists:users,id'
        ];
    }

    static public function responseToFriendRequestRules()
    {
        return [
            'status' => 'required'
        ];
    }

}