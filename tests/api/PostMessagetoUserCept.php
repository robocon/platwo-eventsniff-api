<?php 
$I = new ApiTester($scenario);
$I->wantTo('Send message to user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('message', [
    'access_token' => '1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7',
    'to' => 'users',
    'message' => 'หลวงพี่ แจมสโรชา คอนแท็คเอ๊าะพาสต้า ซูฮกมอนสเตอร์ซีเรียสวัคค์ โบ้ย '.time(),
    'users_id' => ['54d9bc4dda354d757b8b4569'],
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);

