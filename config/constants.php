<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom File For Storing Messages Constants
    |--------------------------------------------------------------------------
    |
    | This file contains all the constants which are used in our application
    | to display any kind of messages
    |
    */

    /* Success Contants */
    'INSERTION_SUCCESS' => 'Data inserted successfully.',
    'UPDATION_SUCCESS' => 'Data updated successfully.',
    'ARCHIVED_SUCCESS' => 'Data archived successfully.',
    'UN_ARCHIVED_SUCCESS' => 'Data unarchived successfully.',
    'DELETION_SUCCESS' => 'Data deleted successfully.',
    'LOGIN_SUCCESS' => 'You have Logged in Successfully.',
    'REGISTER_SUCCESS' => 'You have Registered  Successfully.',
    'DATA_INSERTION_SUCCESS' => 'Data Inserted Successfully.',
    'DATA_UPDATED_SUCCESS' => 'Data Updated Successfully.',
    'SUCCESS_CODE' => 1,
    'TRUE_STATUS' => true,
    'STUART_DELIVERY_SUCCESS' => 'Stuart Delivery Has Been Initiated Successfully, You Can Please Check The Status By Clicking The "Check Status" Button',
    'COMPLETED' => 'Completed',
    'VALID_REFERRAL' => 'Valid referral code.',
   
    /* Failure Contants */
    'INSERTION_FAILED' => 'Failed to insert data.',
    'UPDATION_FAILED' => 'Failed to update data.',
    'ARCHIVED_FAILED' => 'Failed to archive data.',
    'UN_ARCHIVED_FAILED' => 'Failed to unarchive data.',
    'DELETION_FAILED' => 'Failed to delete data.',
    'INVALID_DATA' => 'You have entered invalid or too long data.',
    'FAILED_CODE' => 0,
    'FALSE_STATUS' => false,
    'STATUS_CHANGING_FAILED' => 'Failed to change the activation status.',
    'INVALID_REFERRAL' => 'Invalid referral code.',
    'REFERRAL_CAN_BE_USED_ONCE' => 'Same referral code can be used only once.',
    'REFERRALS_ARE_ONLY_FOR_FIRST_ORDER' => 'Sorry, you can only use a referral before placing your first order.',

    'VALIDATION_ERROR' => 'Validation Error.',
    'INVALID_CREDENTIALS' => 'Invalid Credentials.',

    'EMAIL_NOT_VERIFIED' => 'Email not verified, verify your email first.',

    'ACCOUNT_DEACTIVATED' => 'You are deactivated, kindly contact admin.',

    'NO_RECORD' => 'No Record Found.',
    'NO_SELLER' => 'No seller found against this id.',
    'NO_STORES_FOUND' => 'No stores found in this area.',

    'ORDER_ASSIGNED' => 'Assigned.',
    'ORDER_UPDATED' => 'Updated.',
    'ORDER_CANCELLED' => 'Cancelled.',

    'VALID_PROMOCODE' => 'You have entered a valid promo code.',
    'INVALID_PROMOCODE' => 'Invalid promo code.',
    'EXPIRED_PROMOCODE' => 'This promo code has been expired.',

    'BANK_DETAILS_UPDATED' => 'Bank Account details are successfully updated.',

    'MISSING_OR_INVALID_DATA' => 'Required fields missing or invalid data.',

    'VERIFICATION_SUCCESS' => 'Verification Successful.',
    'VERIFICATION_FAILED' => 'You have entered a invalid verification code.',

    'ITEM_DELETED' => 'Data deleted successfully.',

    'MAX_LIMIT' => 'Promo code usage has reached its maximum limit.',

    'WITHDRAWAL_REQUEST_SUBMITTED' => 'Withdrawal request is successfully submitted.',

    'DATA_ALREADY_EXISTS' => 'Data already exists against id:- ',

    'BUCKET' => 'https://user-imgs.sgp1.digitaloceanspaces.com/',
    /*
    |--------------------------------------------------------------------------
    | Stuart Sandbox Cridentials Key
    |--------------------------------------------------------------------------
    */
    'STUART_SANDBOX_CLIENT_ID' => '7faa9066d638cb94b61f18040355f59ffd124cd94b5444f1ee992d1e3e594a19',
    'STUART_SANDBOX_CLIENT_SECRET' => '9db9dac8282818a4000a75c996fbcb470f8d67835ff26ee442abf4b496ae534b',
    'STUART_SANDBOX_JOBS_URL' => 'https://api.sandbox.stuart.com/v2/jobs',
    'STUART_SANDBOX_TOKEN_URL' => 'https://api.sandbox.stuart.com/oauth/token',
    /*
    |--------------------------------------------------------------------------
    | Stuart Production Cridentials Key
    |--------------------------------------------------------------------------
    */
    'STUART_PRODUCTION_CLIENT_ID' => '75f7341f983c842b6a4847707a1a03d4413687e7223c3f51be34359c2fa9e505',
    'STUART_PRODUCTION_CLIENT_SECRET' => '0144e8a9978851e7005a5a3ef53cba22dc1b6102f49c7add5bb22dedf74c9ba2',
    'STUART_PRODUCTION_JOBS_URL' => 'https://api.stuart.com/v2/jobs',
    'STUART_PRODUCTION_TOKEN_URL' => 'https://api.stuart.com/oauth/token',

    'ADMIN_EMAIL' => 'admin@teekit.co.uk',

    'HTTP_SUCCESS_CODE' => 200,
    'HTTP_OK' => 200,
    'HTTP_SERVER_ERROR' => 500,
    'HTTP_INVALID_ARGUMETS' => 400,
    'HTTP_FORBIDDEN' => 403,
    'HTTP_UNPROCESSABLE_REQUEST' => 422,
    'HTTP_RESOURCE_EXHAUSTED' => 429,
    'HTTP_SERVICE_UNAVAILABLE' => 503,
    'HTTP_GATEWAY_TIMEOUT' => 504,
];