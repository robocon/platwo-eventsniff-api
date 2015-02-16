<?php 
//$user_id = '54ba29c210f0edb8048b457a';
$event_id = '54cb466710f0ed24048b4567';

$I = new ApiTester($scenario);
$I->wantTo('Add log');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '4309946fd4133f4bfc7f79d5881890400f4d59997b0d8b26886641394814cbd2');
$I->sendPOST('log', [
    'reference_id' => $event_id,
//    'user_id' => $user_id,
    'message' => 'test to share to facebook '.time(),
    'type' => 'event',
    'status' => 'share',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
