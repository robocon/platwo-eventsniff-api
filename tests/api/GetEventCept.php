<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events');
//$I->setHeader('access-token', '4d1cee2298152bede6876e16e4086c5c7f1cc23973166a226401cdfccc664cf7');
$I->sendGET('event/5515031df889297f743c9869');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);