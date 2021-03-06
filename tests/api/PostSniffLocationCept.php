<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add log');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '9049734e4ece4a26b62f9fbe67b72117d14cb7a7bc0eb2810482f800bb517277');
$I->sendPOST('sniff/location', [
    'category' => 'all',
//    'category' => [
//        '54c0ad7410f0ed5e048b4572', '54c0ad7410f0ed5e048b4575', '54c0ad7410f0ed5e048b4579'
//    ],
    
    // Test location with center and near
//    'location' => [18.78830184,98.98529291],
    
    // Unit with meters
//    'radius' => 2000,
    
    // Test location with box
    'location' => [[18.776836, 98.969479],[18.800888, 98.999863]],
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);
