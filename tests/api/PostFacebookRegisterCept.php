<?php 
$I = new ApiTester($scenario);
$I->wantTo('Facebook login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('oauth/facebook', [
    'facebook_token' => 'CAAM1TzhIHDwBAGYzm1h4TKZBKqsYcZAVzdWlkJZB8aBrYjpnArgYGltWmBCme5a5aRnKb3qNdQU8QKyCvlij1VQsZCrZBcmfvzyTkHDk1QnMn1VS00TzVI3EBldZBcMEcnqdITYVPTLPcKybejsRBsqUM0M5JsV5ZC2qGW8cAZBLZCII95SmqZAVzTY4LAzmgtxT7vIRy0wOFA6XKT43YdRTYDy6HRbRzT70sZD',
    // Example ios
    'ios_device_token' => [
        'type' => 'dev',
        'key' => 'FE66489F304DC75B8D6E8200DFF8A456E8DAEACEC428B427E9518741C92C6660'
    ]
    // Example Android token
    // "APA91bHh0sDFO9wDD--My4WFltYdx4murTcdwhFaZS928ZIY6x8WXDuO2cVmY5pjaockSYUekpdL8tZxUD-KUGI6QzRXLrS7Jyf7Irknh6ooUgemJ-WdTzmtXu9hG3zl4OSJwM003DRspRrSoY5Fcq8e51b2sheSAUDvNGAvWF8iYtl6ns9Ze9Q", 
        
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$access_token = $I->grabDataFromJsonResponse('access_token');
$I->seeResponseContainsJson([
    'user_id' => $user_id,
    'access_token' => $access_token
]);