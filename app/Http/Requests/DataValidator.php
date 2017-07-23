<?php

namespace App\Http\Requests;

use App\Http\Requests\DataRules as Rules;
use Illuminate\Support\Facades\Validator;

class DataValidator
{
    static public function validate($request)
    {
        if($request->is('/api/register'))
            return Validator::make($request->all(), Rules::registerRules());

        else if($request->is('/api/oauth/register'))
            return Validator::make($request->all(), Rules::oauthRegisterRules());

        else if($request->is('/api/oauth/login'))
            return Validator::make($request->all(), Rules::oauthLoginRules());

        else if($request->is('/api/login'))
            return Validator::make($request->all(), Rules::loginRules());

        else if($request->is('/api/password/send_reset_password'))
            return Validator::make($request->all(), Rules::sendResetPWEmailRules());

        else if($request->is('/api/password/reset/*'))
            return Validator::make($request->all(), Rules::resetPasswordRules());

        else if($request->is('/api/pinpost/create'))
            return Validator::make($request->all(), Rules::pinpostRules());

        else if($request->is('/api/pinvite/create'))
            return Validator::make($request->all(), Rules::pinviteRules());

        else if($request->is('/api/pinvite/uploadPicture/*'))
            return Validator::make($request->all(), Rules::pictureRules());

        else if($request->is('/api/like/create'))
            return Validator::make($request->all(), Rules::likeRulesRules());

        else if($request->is('/api/comment/create'))
            return Validator::make($request->all(), Rules::commentRules());

        else if($request->is('/api/relationship/friend_request'))
            return Validator::make($request->all(), Rules::friendRequestRules());

        else
            return null;
    }

    static public function validateRegister($request)
    {
        return Validator::make($request->all(), Rules::registerRules());
    }

    static public function validateOauthRegister($request)
    {
        return Validator::make($request->all(), Rules::oauthRegisterRules());
    }

    static public function validateOauthLogin($request)
    {
        return Validator::make($request->all(), Rules::oauthLoginRules());
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