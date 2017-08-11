<?php

namespace App\Http\Requests;

class DataRules
{

    static public function registerRules()
    {
        return [
            'username' => 'required|min:3|max:20|alpha_dash|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|alpha_num|min:8|max:16|confirmed',
            'birthday' => 'date_format:Y-m-d'
        ];
    }

    static public function oauthRegisterRules()
    {
        return [
            'username' => 'required|min:3|max:20|unique:users',
            'email' => 'required|email|max:255|unique:email',
            'oauth_type' => 'required|in:facebook,google',
            'picture_url' => 'url',
            'birthday' => 'date_format:Y-m-d'
        ];
    }

    static public function oauthLoginRules()
    {
        return [
            'oauth_type' => 'required|in:facebook,google',
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

    static public function updateProfileRules()
    {
        return [
            'birthday' => 'date_format:Y-m-d',
            'email_privacy' => 'in:self,friends,public',
            'gender_privacy' => 'in:self,friends,public',
            'birthday_privacy' => 'in:self,friends,public'
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
            'privacy' => 'in:self,friends,public'
        ];
    }

    static public function uploadImageRules()
    {
        return [
            'image' => 'required|image'
        ];
    }

    static public function updatePinpostRules()
    {
        return [
            'image' => 'image',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'privacy' => 'in:self,friends,public'
        ];
    }

    static public function fetchPinpostRules()
    {
        return [
            'type' => 'required|in:radius,frame',
            'radius' => 'required_if:type,radius|numeric|min:0',
            'center' => 'required_if:type,radius|valid_coord',
            'top_left' => 'required_if:type,frame|valid_coord',
            'bottom_right' => 'required_if:type,frame|valid_coord',
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

    static public function pictureRules()
    {
        return ['file' => 'image'];
    }

    static public function commentRules()
    {
        return [
            'text' => 'required|min:1'
        ];
    }

    static public function replyRules()
    {
        return [
            'text' => 'required|min:1'
        ];
    }

    static public function friendRequestRules()
    {
        return [
            'requestee_id' => 'required|exists:users,id|numeric'
        ];
    }

    static public function blockUserRules()
    {
        return [
            'user_id' => 'required|exists:users,id|numeric'
        ];
    }

    static public function responseToFriendRequestRules()
    {
        return [
            'status' => 'required|boolean',
            'requester_id' => 'required|exists:users,id|numeric'
        ];
    }

}