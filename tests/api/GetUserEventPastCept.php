<?php 
$I = new ApiTester($scenario);
$I->wantTo('User past events');
$I->sendGET('user/event/past/54c0ad7410f0ed5e048b4567');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);