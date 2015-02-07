<?php
$I = new ApiTester($scenario);
$I->wantTo('Show user detail in Profile Tab');
$I->sendGET('user/54ba29c210f0edb8048b457a');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => $I->grabDataFromJsonResponse('id'),
    'picture' => $I->grabDataFromJsonResponse('picture'),
    'display_name' => $I->grabDataFromJsonResponse('display_name'),
    'email' => $I->grabDataFromJsonResponse('email'),
    'detail' => $I->grabDataFromJsonResponse('detail')
]);