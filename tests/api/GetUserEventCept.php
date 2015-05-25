<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show event on user');
$I->sendGET('user/event/54ed542810f0ed0d048b456a');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);