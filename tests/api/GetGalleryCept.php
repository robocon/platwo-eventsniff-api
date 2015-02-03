<?php
$I = new ApiTester($scenario);
$I->wantTo('Show picture from picture_id');
$I->sendGET('gallery/picture/54cb466810f0ed23048b4567');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);