<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get sniffing around');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendGET('user/sniff_around');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$cities = $I->grabDataFromJsonResponse('cities');
$I->seeResponseContainsJson([
    'cities' => $cities
]);