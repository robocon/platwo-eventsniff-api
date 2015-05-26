<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events');
$I->setHeader('access-token', '1a38cb60a4f60d965759e146299d8ccb01d7a3e7ae36962b551c0df9b976141e');
$I->sendGET('event/550fbd14f88929ec573c9869');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);