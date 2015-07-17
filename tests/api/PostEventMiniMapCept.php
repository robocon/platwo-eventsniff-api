<?php 
$I = new ApiTester($scenario);
$I->wantTo('perform actions and see result');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendPOST('map/minimap', [
//    'category' => 'all',
    'category' => [
        '54c0ad7410f0ed5e048b4567', '54c0ad7410f0ed5e048b456c', '54c0ad7410f0ed5e048b456f'
    ],
    
    // Test location with center and near
//    'location' => [18.78830184,98.98529291],
    
    // Unit with meters
//    'radius' => 2000,
    
    // Test location with box
    'location' => '98.987160,18.788342', // Lng, Lat
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);