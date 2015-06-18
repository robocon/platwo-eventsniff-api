<?php
$I = new ApiTester($scenario);
$I->wantTo('perform actions and see result');
$I->setHeader('access-token', 'e52b84de3dec086207dfa7f949708fdc0a669d7a63a68d5519606f9682c7164a');
$I->sendGET('version');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$title = $I->grabDataFromJsonResponse('title');
$I->seeResponseContainsJson([
    'title' => $title
]);
