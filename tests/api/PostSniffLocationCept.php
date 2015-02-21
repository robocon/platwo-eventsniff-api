<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add log');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '4d1cee2298152bede6876e16e4086c5c7f1cc23973166a226401cdfccc664cf7');
$I->sendPOST('sniff/location', [
//    'category' => 'all',
    'category' => [
        '54c0ad7410f0ed5e048b4572', '54c0ad7410f0ed5e048b4575', '54c0ad7410f0ed5e048b4579'
    ],
    
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
