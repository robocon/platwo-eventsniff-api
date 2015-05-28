<?php 
$I = new ApiTester($scenario);
$I->wantTo('Send message to user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('message', [
    'access_token' => '1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7',
    'to' => 'users',
    'message' => 'เทรดเดบิต ผู้นำไทเฮาเดบิตเบิร์ด พาร์ทเนอร์สามแยกวาทกรรมเวณิกา '.time(),
    'users_id' => ['54d9bc4dda354d757b8b4569','5555a8eeda354dcc068b456b','54ed542810f0ed0d048b456a'],
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);

