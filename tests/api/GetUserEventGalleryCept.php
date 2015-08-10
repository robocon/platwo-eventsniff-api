<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show event and picture for user');
$I->setHeader('access-token', '45dca74e9c85731e846d0ec5527c876af8ec6a63492b8d5fdf559e71537b8925');
$I->sendGET('user/event/pictures');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);