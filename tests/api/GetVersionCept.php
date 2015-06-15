<?php 
$I = new ApiTester($scenario);
$I->wantTo('perform actions and see result');
$I->setHeader('access-token', 'e415ca602afed27260ad1e80af452e04057e345fdd313f9151dbaaffa20d7141');
$I->sendGET('version');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$title = $I->grabDataFromJsonResponse('title');
$I->seeResponseContainsJson([
    'title' => $title
]);