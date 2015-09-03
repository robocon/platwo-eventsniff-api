/**
 * Install NodeJS Follow this link
 * http://www.hostingadvice.com/how-to/install-nodejs-ubuntu-14-04/
 * 
 * Install MongoDB
 * https://www.digitalocean.com/community/tutorials/how-to-connect-node-js-to-a-mongodb-database-on-a-vps
 * 
 * Install Forever
 * http://www.slidequest.com/Taboca/70ang
 */

var http = require('http');
var querystring = require('querystring');

//  npm install mongodb
var MongoClient = require('mongodb').MongoClient;
MongoClient.connect("mongodb://128.199.161.118:27017/eventsniff", function(err, db){
    
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
        
//        console.log(date);

        event_collection.find({
            'build': 1, 
            'approve': 1, 
            'alarm': {'$ne': 0},
            "date_start": {"$lte": date},
            "date_end": {"$gte": date},
            'alarm.active': '1',
            'alarm.alarm_date': date

        },{"_id": 1, "alarm": 1}).toArray(function(err, items){
            
            
            if( typeof(items) != 'undefined' ){
                
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
//                            host: 'http://eventsniff-api.pla2app.com/',
                            hostname: 'http://eventsniff-api.pla2api.com/',
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
            
            } // End if check undefined
        });

    }, 1000); // End interval
});


