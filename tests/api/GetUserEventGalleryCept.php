<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show event and picture for user');
$I->setHeader('access-token', 'ae3cceee925199efb7c98fc50e8285ff88d5a0506156533362ed288b35c5d373');
$I->sendGET('user/event/pictures?user_id=54d9bc4dda354d757b8b4569');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);