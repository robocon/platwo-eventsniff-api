<?php
$I = new ApiTester($scenario);
$I->wantTo('Show picture from picture_id');
$I->sendGET('gallery/picture/54ba191510f0edb7048b456b');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);