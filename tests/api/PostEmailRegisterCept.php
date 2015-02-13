<?php 
$I = new ApiTester($scenario);
$I->wantTo('Email login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register', [
    'email' => 'demouser'.time().'@hotmail.com',
    'username' => 'demo'.time(),
    'password' => '123456',
    'gender' => 'male',
    'birth_date' => '1985-08-30'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);