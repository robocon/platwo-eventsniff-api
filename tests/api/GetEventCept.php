<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events');
$I->sendGET('event/54ba191510f0edb7048b456a');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);