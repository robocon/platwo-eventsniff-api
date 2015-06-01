<?php 
$event_id = '551523e7f889294d1d3c9869';

$I = new ApiTester($scenario);
$I->wantTo('Test post event notification by user manual');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '9e0f594ad0b3f56bbbaa06251feecc2339a53b4e6b027e9d59bb70e2ee773a55');
$I->sendPOST('event/notify/alarm', [
    'event_id' => $event_id,
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);