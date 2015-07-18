<?php
$I = new ApiTester($scenario);
$I->wantTo('Delete picture from picture_id');
$I->setHeader('access-token', '54045a1c5e4acd555ed07c0a0cdc497a2099ef5ef347331144e5450b491fe74d');
$I->sendDELETE('gallery/picture/558fb39c10f0ed21048b4567');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('{"success":true}');