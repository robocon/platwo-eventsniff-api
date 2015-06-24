<?php 
$event_id = '55891e65ef7259f2568b4567';
$I = new ApiTester($scenario);
$I->wantTo('User follow an event');
//$I->setHeader('access-token', '9049734e4ece4a26b62f9fbe67b72117d14cb7a7bc0eb2810482f800bb517277');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
// sniff/follow/:event_id/:user_id
$I->sendPOST('sniff/follow/'.$event_id.'/5582a28110f0ed08048b4567', []);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
//$event_res = $I->grabDataFromJsonResponse('event_id');
$I->seeResponseContainsJson([
    'event_id' => $event_id
]);