<?php 
$I = new ApiTester($scenario);
$I->wantTo('Email login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('oauth/password', [
    'username' => 'wat007',
    'password' => '123456',
//    'username' => 'stan',
//    'password' => '111111',
]);

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);