<?php 
$event_id = '550fbd14f88929ec573c9869';

//54ed542810f0ed0d048b456a
//54edda6b10f0ed15048b4567
$user_id = '54ed542810f0ed0d048b456a';

$I = new ApiTester($scenario);
$I->wantTo('Update Alarm');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('event/alarm/'.$event_id, [
    'active' => '1',
    'user_id' => $user_id,
    'alarm_date' => date('Y-m-d H:i:s'),
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('event_id');
$I->seeResponseContainsJson([
    'event_id' => $success
]);
