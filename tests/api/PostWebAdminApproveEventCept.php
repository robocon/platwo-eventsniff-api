<?php 
$I = new ApiTester($scenario);
$I->wantTo('WebAdmin Approve Event');
$I->sendPOST('webadmin/approve', [
    'event_id' => '55ded638f232e512693dcea7',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('event_id');
$I->seeResponseContainsJson([
    'event_id' => $data
]);