<?php 
$user_id = '54ed542810f0ed0d048b456a';

$I = new ApiTester($scenario);
$I->wantTo('Update User Profile Gender');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/profile/'.$user_id.'/password', [
    'password' => '111111',
    'new_password' => '1234',
    'confirm_password' => '1234',
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);