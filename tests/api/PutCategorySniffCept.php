<?php 
$I = new ApiTester($scenario);
$I->wantTo('Sniff and Unsniff Category');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('category/sniff', [
    'category_id' => '54c0ad7410f0ed5e048b4568',
    'status' => true,
    'access_token' => '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d',
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);