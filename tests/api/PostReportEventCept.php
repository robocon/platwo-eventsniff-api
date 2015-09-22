<?php 
$user_id = '5582a28110f0ed08048b4567';
$reference_id = '55ded78cf232e511693dcea2';

$I = new ApiTester($scenario);
$I->wantTo('Add Report');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendPOST('report', [
    'title' => 'Test edit event title',
    'detail' => 'Test edit event details '.time(),
    'type' => 'e',
//    'user_id' => $user_id,
    'reference_id' => $reference_id
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);