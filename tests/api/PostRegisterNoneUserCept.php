<?php 
$I = new ApiTester($scenario);
$I->wantTo('Register none user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register/noneuser', [
    'ios_device_token' => [
        'type' => 'product',
        'key' => 'FE66489F304DC75B8D6E8200DFF8A456E8DAEACEC428B427E9518741C92C6660'
    ]
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $id
]);