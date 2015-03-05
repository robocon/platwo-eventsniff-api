<?php 
$I = new ApiTester($scenario);
$I->wantTo('Email login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register', [
    'email' => 'roboconk@gmail.com',
    'username' => 'roboconk',
    'password' => '123456',
    'gender' => 'female',
    'birth_date' => '1900-01-01',
//    'android_token' => 'APA91bEQkWLN5GN8VXZIyIq9lFFiKcig4rTQEbK_JHjxXqpW5JnG3U_XeFaN5Wq7Igf9mRRkFRC0gUrEtf0IyIdPKbtkYNsKxmxlAh5ytfaEvAd3SQgAYg2kknkGE7VUYa6Pnic0YvU8HMM8kUNcYkIoHoIOPKyi0xKC9qUUCrnS1vb80fsiljU'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);