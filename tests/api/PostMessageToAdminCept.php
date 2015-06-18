<?php 
$I = new ApiTester($scenario);
$I->wantTo('Send message to admin');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('contact/comment', [
    'access_token' => 'e415ca602afed27260ad1e80af452e04057e345fdd313f9151dbaaffa20d7141',
    'message' => 'หลวงพี่ แจมสโรชา คอนแท็คเอ๊าะพาสต้า ซูฮกมอนสเตอร์ซีเรียสวัคค์ โบ้ย '.time(),
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);
