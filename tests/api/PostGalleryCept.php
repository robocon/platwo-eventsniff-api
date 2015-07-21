<?php 

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));
$user_id = '5582a28110f0ed08048b4567';
$event_id = '55aca58cf232e58c2c42266b';

$I = new ApiTester($scenario);
$I->wantTo('Add picture into event');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('gallery/'.$event_id, [
    'event_id' => $event_id,
    'picture' => $image,
    'user_id' => $user_id,
    'detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit '.time()
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'user_id' => $user_id,
    'event_id' => $event_id,
]);
