<?php 
$I = new ApiTester($scenario);
$I->wantTo('Category Search');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('category/search', [
    'word' => 'krit',
    'access_token' => '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);