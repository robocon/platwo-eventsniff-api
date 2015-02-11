<?php 
$I = new ApiTester($scenario);
$I->wantTo('Email login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register', [
    'email' => 'demouser'.time().'@hotmail.com',
    'username' => 'demo'.time(),
    'password' => '1234',
    'gender' => 'male',
    'birth_date' => '1985-08-30',
    'country' => '54b8dfa810f0edcf048b4567',
    'city' => '54b8e0e010f0edcf048b4569'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);