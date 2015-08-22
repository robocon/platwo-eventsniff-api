<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get now event in there category');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendGET('category/now/54c0ad7410f0ed5e048b4567');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);