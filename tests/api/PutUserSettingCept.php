<?php 
$user_id = '54ba29c210f0edb8048b457a';

$I = new ApiTester($scenario);
$I->wantTo('Update User Profile Gender');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/setting/'.$user_id.'/phone', [
    'enable' => '0'
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);