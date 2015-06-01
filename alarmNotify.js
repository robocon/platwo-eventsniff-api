var http = require('http');
var querystring = require('querystring');
var MongoClient = require('mongodb').MongoClient;
MongoClient.connect("mongodb://110.164.70.44:27017/eventsniff", function(err, db){
    
    // Return if has an error
    if(err){
        return console.dir(err);
    }

    var event_collection = db.collection('event');
    var user_collection = db.collection('users');
    
    var inter = setInterval(function(){
        
        var date = new Date();
//        date.setUTCHours(7);
        date.setMilliseconds(0);
        
        console.log(date);

        event_collection.find({
            "build": 1, 
            "approve": 1, 
            "alarm":{"$ne": 0}, 
            "date_end": {"$gte": date},
            "alarm.alarm_date": {"$eq": date}
        },{"_id": 1, "alarm": 1}).toArray(function(err, items){
            
            items.forEach(function(item){
                
                item.alarm.forEach(function(puser){
                    
                    var id = require('mongodb').ObjectID(puser.user_id);
                    user_collection.findOne({
                        "_id": id
                    },{"access_token": 1}, function(err, user){
                        
                        // Set query string
                        var post_data = querystring.stringify({
                            'event_id': item._id.toString(),
                            'access_token': user.access_token
                        });
                        
                        // Set post options
                        var post_options = {
    //                        host: 'http://eventsniff-api.pla2app.com/',
                            hostname: 'eventsniff.dev',
                            port: '80',
                            path: '/event/notify/alarm',
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Content-Length': post_data.length
                            }
                        };
                        
                        var post_req = http.request(post_options, function(res) {
                            res.setEncoding('utf8');
                            res.on('data', function (chunk) {
                                console.log('Response: ' + chunk);
                            });
                        });
                        
                        post_req.on('error', function(e) {
                            console.log('problem with request: ' + e.message);
                        });

                        post_req.write(post_data);
                        post_req.end();
                        
                    });
                    
                   
                }); // End foreach alarm
                
            }); // End foreach event
        });

    }, 1000); // End interval
});


