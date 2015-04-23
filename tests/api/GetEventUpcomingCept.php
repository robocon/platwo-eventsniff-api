<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events upcoming');
$I->setHeader('country', '54b8dfa810f0edcf048b4567');
$I->setHeader('city', '54b8e0e010f0edcf048b4575');
$I->sendGET('event/upcoming');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);