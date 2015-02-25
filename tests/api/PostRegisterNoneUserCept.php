<?php 
$I = new ApiTester($scenario);
$I->wantTo('Register none user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register/noneuser', [
    'ios_device_token' => [
        'type' => 'product',
        'key' => '56b3cf33d566d42e22457698f3d935ddbdd3fc26bc50330e7813f7d935795c4e'
    ]
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $id
]);