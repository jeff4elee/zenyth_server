<?php

/* General Constants */


define("DELETE_FAIL", "Delete Failed");

define("NOT_FOUND", "Not found");

define("REQUIRES_ACCESS_TOKEN", "This action requires an access token");

define("INVALID_EMAIL", "Invalid email provided");

define("INVALID_TOKEN", "Invalid access token");

define("INVALID_OAUTH_TOKEN", "Invalid oauth token");

define("INVALID_OAUTH_EMAIL", "Email provided does not match email retrieved from the oauth token");

define("OAUTH_NO_EMAIL_ACCESS", "Server was not able to retrieve email from this access token");

define("NULL_URL", "No URL was provided");

define("REQUEST_NO_RESPONSE", "Unable to get a response");

define("INVALID_IMAGE_TYPE", "Image file type can only be jpg, jpeg, png, or gif");

define("UPLOAD_SUCCESS", "Successfully uploaded file");

define("OBJECT_FAIL_TO_CREATE", "Could not create object");

define("NOT_USERS_OBJECT", "This %s object does not belong to the user with this access token");

define("OBJECT_NOT_FOUND", "This %s object does not exist");

define("DELETE_SUCCESS", "This %s object was successfully deleted");

define("CHECK_EMAIL", "Check your email");

define("INVALID_USER_ID", "Invalid user id");

define("CLIENT_ID_REQUIRED", "A client ID is required to access API");

define("INVALID_CLIENT_ID", "The client ID provided is invalid");

/* LoginController */
define("LOGIN_INVALID_CREDENTIAL", "The login credential you provide is invalid");

/* OauthController */
define("MERGE_GOOGLE", 'A Google account with the same email has already been created. Do you want to merge?');

define("MERGE_FACEBOOK", "A Facebook account with the same email has already been created. Do you want to merge?");

define("MERGE_ACCOUNT", "An account with the same email has already been created. Do you want to merge?");

/* RegisterController */
define("ACCOUNT_VERIFIED", "Your account has been verified");

/* RelationshipController */
define("NO_PENDING_REQUEST", "No pending request");

define("IGNORED_FOLLOWER_REQUEST", "Friend request ignored");

define("INVALID_REQUEST_TO_SELF", "Cannot send this request to yourself");

define("EXISTED_RELATIONSHIP", "This relationship already exists");

define("USER_BLOCKING_NOT_EXIST", "The user being blocked does not exist");

define("USER_DELETING_NOT_EXIST", "The user being deleted does not exist");

define("USER_BLOCKED", "Cannot delete a follower that is blocked");

/* LikeController */
define("ALREADY_LIKED_ENTITY", "Entity has already been liked by this user");

/* ForgotPasswordController */
define("RESET_PW_SUCCESS", "Successfully reset password");

/* Repository */
define("EITHER_MODEL_OR_ID", "Must provide either Eloquent model or ID of the object");

/* Objects' Name */
define("LIKE", "Like");
define("PINPOST", "Pinpost");
define("COMMENT", "Comment");
define("REPLY", "Reply");
define("IMAGE", "Image");
define("RELATIONSHIP", "Relationship");
define("USER", "User");
define("TAG", "Tag");