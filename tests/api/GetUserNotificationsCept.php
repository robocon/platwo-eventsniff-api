<?php 
$I = new ApiTester($scenario);
$I->wantTo('perform actions and see result');
$I->setHeader('access-token', '448c8abc7b7f392963f5ed26131d9f3590dc6edd7b411af4c2565123c60e9c24');

//$I->sendGET('user/notify?access_token=1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7');
$I->sendGET('user/notify');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);

