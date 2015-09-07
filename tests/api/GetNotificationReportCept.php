<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get report notifications');
$I->setHeader('access-token', 'c0eb124c9a9a40c37bfb3271eba162ea84867a4abd945aa9dd67701d485de860');
$I->sendGET('notification/reports');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);