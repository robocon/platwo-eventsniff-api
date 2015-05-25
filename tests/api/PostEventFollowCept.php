<?php 
$event_id = '55631bf410f0edcb0a8b4567';
$I = new ApiTester($scenario);
$I->wantTo('Event search');
//$I->setHeader('access-token', '9049734e4ece4a26b62f9fbe67b72117d14cb7a7bc0eb2810482f800bb517277');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
// sniff/follow/:event_id/:user_id
$I->sendPOST('sniff/follow/'.$event_id.'/54ed542810f0ed0d048b456a', []);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
//$event_res = $I->grabDataFromJsonResponse('event_id');
$I->seeResponseContainsJson([
    'event_id' => $event_id
]);