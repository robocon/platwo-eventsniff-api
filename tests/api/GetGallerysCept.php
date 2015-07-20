<?php
$I = new ApiTester($scenario);
$I->wantTo('Show pictures from event_id');
$I->sendGET('gallery/5595063810f0ed0c048b456e');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);