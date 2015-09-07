<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get report item');
$I->setHeader('access-token', 'c0eb124c9a9a40c37bfb3271eba162ea84867a4abd945aa9dd67701d485de860');
$I->sendGET('notification/report/55eaaa6f10f0eded098b456f');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $data
]);