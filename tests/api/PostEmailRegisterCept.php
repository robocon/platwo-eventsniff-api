<?php 
$I = new ApiTester($scenario);
$I->wantTo('Email login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->setHeader('access-token', '589ac58201a6fc08f4514ed7cd1d2781a25815611853b84484a61bfcc1be0417');

$I->sendPOST('register', [
    'email' => 'stan.southpark@gmail.com',
    'username' => 'stan',
    'password' => '123456',
    'gender' => 'female',
    'birth_date' => '1985-03-18',
]);
//$I->sendPOST('register', [
//    'email' => 'roboconk@gmail.com',
//    'username' => 'roboconk',
//    'password' => '123456',
//    'gender' => 'female',
//    'birth_date' => '1985-03-18',
//]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);