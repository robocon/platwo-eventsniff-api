<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events');
//$I->setHeader('access-token', '9049734e4ece4a26b62f9fbe67b72117d14cb7a7bc0eb2810482f800bb517277');
$I->sendGET('event/5556c4adf889296d373c9869');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);