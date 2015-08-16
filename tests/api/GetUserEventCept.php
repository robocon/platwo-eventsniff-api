<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show event on user');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendGET('user/event?user_id=54d9bc4dda354d757b8b4569');
//$I->sendGET('user/event');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);