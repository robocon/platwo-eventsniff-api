<?php
$I = new ApiTester($scenario);
$I->wantTo('Show today event');
$I->sendGET('event/today_event/en');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);
