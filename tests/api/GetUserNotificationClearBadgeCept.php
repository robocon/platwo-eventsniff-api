<?php 
$I = new ApiTester($scenario);
$I->wantTo('perform actions and see result');
//$I->setHeader('access-token', 'c69a4407edefecf237cd616a773c0194f89b15bae581ac0dc7dc36ce74c6f6f8');

$I->sendGET('user/notify/clear_badge?access_token=1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);