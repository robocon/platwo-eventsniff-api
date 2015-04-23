<?php 
$I = new ApiTester($scenario);
$I->wantTo('Send message to user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('message', [
    'access_token' => '9049734e4ece4a26b62f9fbe67b72117d14cb7a7bc0eb2810482f800bb517277',
    'to' => 'users',
    'message' => 'test seand a message '.time(),
    'users_id' => ['54d9bc4dda354d757b8b4569'],
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);

