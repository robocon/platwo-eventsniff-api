define({ "api": [
  {
    "type": "get",
    "url": "/event",
    "title": "GET /event",
    "description": "<p>Get all event</p> ",
    "name": "GetEvents",
    "group": "Event",
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "post",
    "url": "/event/gallery/:id",
    "title": "POST /event/gallery/:id",
    "description": "<p>Save picture after first picture</p> ",
    "name": "PostAddEventGallery",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Event id</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "picture",
            "description": "<p>Picture in base64_encode</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "user_id",
            "description": "<p>User id</p> "
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "post",
    "url": "/event/gallery",
    "title": "POST /event/gallery",
    "description": "<p>Booking event with first picture</p> ",
    "name": "PostEventGallery",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "picture",
            "description": "<p>Picture in base64_encode</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "user_id",
            "description": "<p>User id</p> "
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "put",
    "url": "/event/:id",
    "title": "PUT /event/:id",
    "description": "<p>Update event details</p> ",
    "name": "PutEvent",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Event id</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "name",
            "description": "<p>Event name</p> "
          },
          {
            "group": "Parameter",
            "type": "Text",
            "optional": false,
            "field": "detail",
            "description": "<p>Event description</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "date_start",
            "description": "<p>Event datetime E.g. 2014-01-15 11:00:00</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "date_end",
            "description": "<p>Event datetime</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "credit",
            "description": "<p>Something where are you get this event from</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "user_id",
            "description": "<p>User id</p> "
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/location/cities/:country_id",
    "title": "GET /location/cities/:country_id",
    "description": "<p>Get all city from country</p> ",
    "name": "LocationCities",
    "group": "Location",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n     \"data\": [\n         {\n             \"id\": \"54b8e0e010f0edcf048b4568\",\n             \"name\" \"Krabi\",\n         },\n         {\n             \"id\": \"54b8e0e010f0edcf048b4569\",\n             \"name\" \"Bangkok\",\n         },\n         ...\n     ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/LocationCTL.php",
    "groupTitle": "Location"
  },
  {
    "type": "get",
    "url": "/location/countries",
    "title": "GET /location/countries",
    "description": "<p>Get all country</p> ",
    "name": "LocationCountries",
    "group": "Location",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n     \"data\": [\n         {\n             \"id\": \"54b8dfa810f0edcf048b4567\",\n             \"name\" \"thailand\",\n         },\n         ...\n     ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/LocationCTL.php",
    "groupTitle": "Location"
  },
  {
    "type": "post",
    "url": "/oauth/facebook",
    "title": "POST /oauth/facebook",
    "description": "<p>Register with facebook</p> ",
    "name": "OauthFacebook",
    "group": "OAuth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "facebook_token",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "ios_device_token",
            "description": "<p>Token from your mobile</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "android_token",
            "description": "<p>Token from your mobile</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"user_id\": \"54506d34da354df2078b4569\",\n \"access_token\": \"9f0f853517eaaed3c0b74838e6e95693\",\n \"type\": \"normal\",\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/OAuthCTL.php",
    "groupTitle": "OAuth"
  },
  {
    "type": "get",
    "url": "/sniff/category",
    "title": "GET /sniff/category",
    "description": "<p>Get all category</p> ",
    "name": "SniffCategory",
    "group": "Sniff",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n     \"length\": 19,\n     \"data\": [\n         {\n             \"id\": \"6f2da37e72bf9e100b40567c\",\n             \"name\" \"Awards\",\n         },\n         {\n             \"id\": \"e9c5c932c205770da433d3de\",\n             \"name\" \"Conferences\",\n         },\n         ...\n     ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/SniffCTL.php",
    "groupTitle": "Sniff"
  },
  {
    "type": "post",
    "url": "/register",
    "title": "POST /register",
    "description": "<p>Register with email</p> ",
    "name": "Register",
    "group": "User",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "username",
            "description": "<p>Your username using for login to system</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "email",
            "description": "<p>Email address</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>Your password</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "gender",
            "description": "<p>male or female</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "birth_date",
            "description": "<p>Your birth date</p> "
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/RegisterCTL.php",
    "groupTitle": "User"
  },
  {
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "optional": false,
            "field": "varname1",
            "description": "<p>No type.</p> "
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "varname2",
            "description": "<p>With type.</p> "
          }
        ]
      }
    },
    "type": "",
    "url": "",
    "version": "0.0.0",
    "filename": "./doc/main.js",
    "group": "_home_robocon_pla2_eventsniff_doc_main_js",
    "groupTitle": "_home_robocon_pla2_eventsniff_doc_main_js",
    "name": ""
  }
] });