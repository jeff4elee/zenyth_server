<?php

namespace App\Http\Requests;

use App\Http\Requests\DataRules as Rules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DataValidator
{
    static public function validate(Request $request)
    {
        if($request->is('api/oauth/register'))
            return Validator::make($request->all(), Rules::oauthRegisterRules());

        else if($request->is('api/register'))
            return Validator::make($request->all(), Rules::registerRules());

        else if($request->is('api/oauth/login'))
            return Validator::make($request->all(), Rules::oauthLoginRules());

        else if($request->is('api/login'))
            return Validator::make($request->all(), Rules::loginRules());

        else if($request->is('api/password/send_reset_password'))
            return Validator::make($request->all(), Rules::sendResetPWEmailRules());

        else if($request->is('api/user/search_user'))
            return Validator::make($request->all(), Rules::searchUserRules());

        else if($request->is('api/profile/profile_picture/upload*'))
            return Validator::make($request->all(), Rules::uploadImageRules());

        else if($request->is('api/profile/update'))
            return Validator::make($request->all(), Rules::updateProfileRules());

        else if($request->is('api/pinpost/create'))
            return Validator::make($request->all(), Rules::createPinpostRules());

        else if($request->is('api/pinpost/update/*'))
            return Validator::make($request->all(), Rules::updatePinpostRules());

        else if($request->is('api/pinpost/upload_image'))
            return Validator::make($request->all(), Rules::uploadImageRules());

        else if($request->is('api/pinpost/comment/create/*'))
            return Validator::make($request->all(), Rules::commentRules());

        else if($request->is('api/comment/update/*'))
            return Validator::make($request->all(), Rules::commentRules());

        else if($request->is('api/comment/upload_image/*'))
            return Validator::make($request->all(), Rules::uploadImageRules());

        else if($request->is('api/reply/create/*'))
            return Validator::make($request->all(), Rules::replyRules());

        else if($request->is('api/reply/update/*'))
            return Validator::make($request->all(), Rules::replyRules());

        else if($request->is('api/reply/upload_image/*'))
            return Validator::make($request->all(), Rules::uploadImageRules());

        else if($request->is('api/pinpost/fetch')) {
            $coordError = 'Geographic coordinate must be in the form {lat,long}'
                .' and satisfies the geographic coordinate rules';
            $messages = [
                'center.valid_coord' => $coordError,
                'first_coord.valid_coord' => $coordError,
                'second_coord.valid_coord' => $coordError,
            ];
            return Validator::make($request->all(), Rules::fetchPinpostRules(),
                $messages);
        }

        else if($request->is('api/pinpost/feed'))
            return Validator::make($request->all(), Rules::fetchFeedRules());

        else if($request->is('api/tag/search') || $request->is('api/tag/info'))
            return Validator::make($request->all(), Rules::searchTagRules());

        else if($request->is('api/relationship/friend_request'))
            return Validator::make($request->all(), Rules::friendRequestRules());

        else if($request->is('api/relationship/response'))
            return Validator::make($request->all(), Rules::responseToFriendRequestRules());

        else if($request->is('api/relationship/block_user'))
            return Validator::make($request->all(), Rules::blockUserRules());

        else if($request->is('api/client_id'))
            return Validator::make($request->all(), Rules::generateClientIdRules());

        else
            return null;
    }

    static public function validateRestorePassword($request) {
        return Validator::make($request->all(), Rules::resetPasswordRules());
    }

}