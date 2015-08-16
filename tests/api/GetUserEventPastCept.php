<?php 
$I = new ApiTester($scenario);
$I->wantTo('User past events');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendGET('user/event/past');
//$I->sendGET('user/event/past?user_id=55cc2bb9f232e52848055b11');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);