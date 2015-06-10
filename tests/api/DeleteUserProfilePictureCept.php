<?php 
$I = new ApiTester($scenario);
$I->wantTo('Delete picture of user profile');
$I->setHeader('access-token', '1dc7db50bf23d72bedd355ebfc5bc9397a75d3082045196bff46bb07421fddd7');
$I->sendDELETE('user/profile');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('{"success":true}');
