<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get user notification setting');
$I->setHeader('access-token', 'e415ca602afed27260ad1e80af452e04057e345fdd313f9151dbaaffa20d7141');

$I->sendGET('user/notification');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$alarm = $I->grabDataFromJsonResponse('alarm');
$I->seeResponseContainsJson([
    'alarm' => $alarm
]);