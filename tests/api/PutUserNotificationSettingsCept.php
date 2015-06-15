<?php 
$I = new ApiTester($scenario);
$I->wantTo('Update notification setting');
$I->setHeader('access-token', 'e415ca602afed27260ad1e80af452e04057e345fdd313f9151dbaaffa20d7141');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/notification', [
    'type' => 'checkin',
    'status' => 'false'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
