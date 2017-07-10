<?php

namespace App\Http\Requests;

use App\Http\Requests\DataRules as Rules;
use Illuminate\Support\Facades\Validator;

class DataValidator
{

    static public function validateRegister($request)
    {
        return Validator::make($request->all(), Rules::registerRules());
    }

    static public function validateLogin($request)
    {
        return Validator::make($request->all(), Rules::loginRules());
    }

    static public function validateResetPasswordEmail($request)
    {
        return Validator::make($request->all(), Rules::sendResetPWEmailRules());
    }

    static public function validateResetPassword($request)
    {
        return Validator::make($request->all(), Rules::resetPasswordRules());
    }

    static public function validatePinpost($request)
    {
        return Validator::make($request->all(), Rules::pinpostRules());
    }

    static public function validatePinvite($request)
    {
        return Validator::make($request->all(), Rules::pinviteRules());
    }

    static public function validatePicture($request)
    {
        return Validator::make($request->all(), Rules::pictureRules());
    }

    static public function validateLike($request)
    {
        return Validator::make($request->all(), Rules::likeRules());
    }

    static public function validateComment($request)
    {
        return Validator::make($request->all(), Rules::commentRules());
    }

    static public function validateFriendRequest($request)
    {
        return Validator::make($request->all(), Rules::friendRequestRules());
    }

}