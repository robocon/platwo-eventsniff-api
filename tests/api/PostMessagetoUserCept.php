<?php 
$I = new ApiTester($scenario);
$I->wantTo('Send message to user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('message', [
    'access_token' => '3cd42328e92c3caffa9267db101eeb35ab9c3f5a2126cd4f0e50f94f5429c303',
    'to' => 'users',
    'message' => 'ทดสอบส่งข้อความจ้าาาาาาาาาาาา  '.time(),
    'users_id' => ['54d9bc4dda354d757b8b4569'],
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);

