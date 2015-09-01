<?php 
$user_id = '54d9bc4dda354d757b8b4569';
$event_id = '55ded638f232e512693dcea7';

$I = new ApiTester($scenario);
$I->wantTo('Add Report');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', 'f1501fa3347169a033b2240c5466a8af4c69e6c37f23eb15304de6b8649512cd');
$I->sendPOST('report', [
    'detail' => 'Testing message detail '.time(),
    'type' => 'event',
    'user_id' => $user_id,
    'reference_id' => $event_id
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);