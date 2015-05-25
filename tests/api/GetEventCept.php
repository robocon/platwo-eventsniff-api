<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events');
$I->setHeader('access-token', '1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7');
$I->sendGET('event/55631bf410f0edcb0a8b4567');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);