<?php 
$event_id = '550fbd14f88929ec573c9869';

$I = new ApiTester($scenario);
$I->wantTo('Test post event notification by user manual');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7');
$I->sendPOST('event/notify/alarm', [
    'event_id' => $event_id,
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);