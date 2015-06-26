<?php 
$I = new ApiTester($scenario);
$I->wantTo('Set sniffing around ');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/sniff_around', [
    'cities' => [
        '54b8e0e010f0edcf048b4569', '54b8e0e010f0edcf048b4575', '54b8e0e010f0edcf048b459e'
    ],
    'access_token' => '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$cities = $I->grabDataFromJsonResponse('cities');
$I->seeResponseContainsJson([
    'cities' => $cities
]);

