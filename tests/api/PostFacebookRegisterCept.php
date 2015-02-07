<?php 
$I = new ApiTester($scenario);
$I->wantTo('Facebook login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('oauth/facebook', [
    'facebook_token' => 'CAAM1TzhIHDwBALHxQBjtIp5NW1fTwcyvCgSgmheFms4574sYPvLvwyD6elqWQ3Es6gLI3bHYjVsydbfaKubXhDTsvZBCJdeWBnHkFkB7gdtAZClYJSZCMGmPZCdfgZAu0Rh5pBxSgoNQLxu27RSYqiUfmbpGfUdkAIRrnuaRQBwbDT9ReiL4IigMdQs8nCZBVHaDXdIxGkSaej2BYQZCWhkxHhmiR38iNEZD',
    'ios_device_token' => 'FE66489F304DC75B8D6E8200DFF8A456E8DAEACEC428B427E9518741C92C6660',
    'country' => '54b8dfa810f0edcf048b4567',
    'city' => '54b8e0e010f0edcf048b4569'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$access_token = $I->grabDataFromJsonResponse('access_token');
$I->seeResponseContainsJson([
    'user_id' => $user_id,
    'access_token' => $access_token
]);