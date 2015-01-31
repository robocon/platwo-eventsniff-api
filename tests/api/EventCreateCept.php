<?php 
// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));
$user_id = '54ba29c210f0edb8048b457a';

/**
 * Booking an event
 */
$I = new ApiTester($scenario);
$I->wantTo('Booking an event');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('event/gallery', [
    'picture' => $image,
    'user_id' => $user_id
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$event_id = $I->grabDataFromJsonResponse('event_id');
$picture = $I->grabDataFromJsonResponse('picture');

$I->seeResponseContainsJson([
    'user_id' => '54ba29c210f0edb8048b457a',
    'event_id' => $event_id,
    'picture' => $picture
]);

/**
 * Add more picture into event
 */
$I->wantTo('Add more gallery into event');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('event/gallery/'.$event_id, [
    'event_id' => $event_id,
    'picture' => $image,
    'user_id' => $user_id
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'user_id' => '54ba29c210f0edb8048b457a',
    'event_id' => $event_id,
]);

/**
 * Update event data
 */
$test_time = time();
$test_date_start = date('Y-m-d H:i:s');
$test_date_end = date('Y-m-d H:i:s', strtotime('+2 week'));
//$test_date_start = date('Y-m-d H:i:s', strtotime('+1 week'));
//$test_date_end = date('Y-m-d H:i:s', strtotime('+2 week'));

$I->wantTo('Update an event data');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$put = [
    'name' => 'test add name '.$test_time,
    'detail' => 'test add detail '.$test_time,
    'date_start' => $test_date_start,
    'date_end' => $test_date_end,
    'credit' => 'http://'.$test_time.'.pla2.com/test',
    'user_id' => $user_id,
    'location' => '18.826562,98.892365',
    'location_name' => 'test location '.$test_time,
    'tags' => [
//        '54c0ad7410f0ed5e048b4572', '54c0ad7410f0ed5e048b4573', '54c0ad7410f0ed5e048b4574'
        '54c0ad7410f0ed5e048b456a'
    ],
    'lang' => 'en'
];
$I->sendPUT('event/'.$event_id, $put);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$tags = $I->grabDataFromJsonResponse('tags');
$time_edit = $I->grabDataFromJsonResponse('time_edit');
$I->seeResponseContainsJson([
    'name' => $put['name'],
    'detail' => $put['detail'],
    'date_start' => $put['date_start'],
    'date_end' => $put['date_end'],
    'credit' => $put['credit'],
    'time_edit' => $time_edit,
    'id' => $event_id,
    'tags' => $tags,
    'location' => [
        'name' => $put['location_name'],
        'position' => '18.826562,98.892365'
        ],
    'status' => 200
]);