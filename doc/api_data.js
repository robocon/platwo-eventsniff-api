define({ "api": [
  {
    "type": "post",
    "url": "/comment/:event_id",
    "title": "POST /comment/:event_id",
    "description": "<p>Comment into event</p> ",
    "name": "CommentSave",
    "group": "Comment",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "event_id",
            "description": "<p>Event id</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "detail",
            "description": "<p>Comment details</p> "
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
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"detail\": \"hello world\",\n \"user_id\": \"54ba29c210f0edb8048b457a\",\n \"event_id\": \"54ba191510f0edb7048b456a\",\n \"time_stamp\": \"2015-01-21 11:09:11\",\n \"user\": {\n     \"name\": \"Kritsanasak Kuntaros\",\n     \"picture\": {\n         \"id\": \"54ba8cd690cc1350158b4619jpg\",\n         \"width\": 180,\n         \"height\": 180,\n         \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba8cd690cc1350158b4619jpg\\/\"\n     }\n },\n \"id\": \"54bf266710f0ed12048b456a\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/CommentCTL.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "/comment/:event_id",
    "title": "GET /comment/:event_id",
    "description": "<p>Get comments from event_id</p> ",
    "name": "GetComment",
    "group": "Comment",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "event_id",
            "description": "<p>User id</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "page",
            "description": "<p>(Optional) Pagination length</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "limit",
            "description": "<p>(Optional) Limit an event when show from each page</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"data\": [\n        {\n            \"detail\": \"test comment 1422678850\",\n            \"time_stamp\": \"2015-01-31 11:34:10\",\n            \"id\": \"54cc5b4210f0ed21048b456c\",\n            \"user\": {\n            \"display_name\": \"Kritsanasak Kuntaros\",\n            \"picture\": {\n                \"id\": \"54ba8cd690cc1350158b4619jpg\",\n                \"width\": 180,\n                \"height\": 180,\n                \"url\": \"http://110.164.70.60/get/54ba8cd690cc1350158b4619jpg/\"\n            },\n            \"id\": \"54ba29c210f0edb8048b457a\"\n        }\n        },{...}\n    ],\n    \"length\": 10,\n    \"total\": 23,\n    \"prev_count\": 3,\n    \"paging\": {\n        \"next\": \"http://eventsniff.dev/comment/54ba191510f0edb7048b456a?page=2&limit=10\",\n        \"prev\": \"http://eventsniff.dev/comment/54ba191510f0edb7048b456a?page=1&limit=10\"\n    }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/CommentCTL.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "/event/:event_id",
    "title": "GET /event/:event_id",
    "description": "<p>Get event from id [not complete yet]</p> ",
    "name": "GetEvent",
    "group": "Event",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"alarm\": 0,\n    \"approve\": 1,\n    \"build\": 1,\n    \"credit\": \"https:\\/\\/www.google.com\",\n    \"date_end\": \"1970-01-01 07:00:00\",\n    \"date_start\": \"1970-01-01 07:00:00\",\n    \"detail\": \"Example detail\",\n    \"name\": \"Example title\",\n    \"time_edit\": \"1970-01-01 07:00:00\",\n    \"time_stamp\": \"1970-01-01 07:00:00\",\n    \"user_id\": \"1\",\n    \"id\": \"54ba191510f0edb7048b456a\",\n    \"pictures\": [\n        {\n            \"id\": \"54ba7c3590cc13ab048b4628png\",\n            \"width\": 100,\n            \"height\": 100,\n            \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba7c3590cc13ab048b4628png\\/\"\n        },\n        {...}\n    ],\n    \"total_sniffer\": 2,\n    \"sniffer\": [\n    {\n        \"id\": \"54ba29c210f0edb8048b457a\",\n        \"picture\": {\n            \"id\": \"54ba8cd690cc1350158b4619jpg\",\n            \"width\": 180,\n            \"height\": 180,\n            \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba8cd690cc1350158b4619jpg\\/\"\n        }\n    }\n    ],\n    \"total_comment\": 2,\n    \"comments\": [\n        {\n            \"detail\": \"hello world\",\n            \"user_id\": \"54ba29c210f0edb8048b457a\",\n            \"event_id\": \"54ba191510f0edb7048b456a\",\n            \"time_stamp\": \"2015-01-21 11:09:11\",\n            \"id\": \"54bf266710f0ed12048b456a\",\n            \"user\": {\n                \"display_name\": \"Kritsanasak Kuntaros\",\n                \"picture\": {\n                    \"id\": \"54ba8cd690cc1350158b4619jpg\",\n                    \"width\": 180,\n                    \"height\": 180,\n                    \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba8cd690cc1350158b4619jpg\\/\"\n                }\n            }\n        },\n        {...}\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event/category_lists/:lang",
    "title": "GET /event/category_lists/:lang",
    "description": "<p>List an event that is not empty</p> ",
    "name": "GetEventCategory",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "lang",
            "description": "<p>Language like en, th. Default is en</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"data\": [\n        {\n            \"id\": \"54c0ad7410f0ed5e048b4567\",\n            \"name\": \"Promotion\"\n            \"thumb\": {\n                \"id\": \"54ba7edc90cc137f238b45ffpng\",\n                \"width\": 100,\n                \"height\": 100,\n                \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba7edc90cc137f238b45ffpng\\/\"\n            }\n        },\n        {...},\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event/category_set/:category_id",
    "title": "GET /event/category_set/:category_id",
    "description": "<p>Get event from category on today and in range</p> ",
    "name": "GetEventCategorySet",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "category_id",
            "description": "<p>Category Id</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"data\": [\n    {\n        \"date_start\": \"2015-01-29 10:49:15\",\n        \"name\": \"test add name 1422503355\",\n        \"id\": \"54c9adb810f0ed5b048b4568\",\n        \"category\": \"54c0ad7410f0ed5e048b4572\",\n        \"thumb\": {\n            \"id\": \"54ca10c790cc13aa048b461apng\",\n            \"width\": 25,\n            \"height\": 25,\n            \"url\": \"http://110.164.70.60/get/54ca10c790cc13aa048b461apng/\"\n        }\n    },\n    {...}\n    ],\n    \"length\": 2\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event/category/upcoming/:category_id",
    "title": "GET /event/category/upcoming/:category_id",
    "description": "<p>Get event list when click from /event/category_lists/:lang</p> ",
    "name": "GetEventCategoryUpcoming",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "category_id",
            "description": "<p>Category Id</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"data\": [\n    {\n        \"date_end\": \"2015-02-11 17:13:01\",\n        \"date_start\": \"2015-02-04 17:13:01\",\n        \"name\": \"test add name 1422439981\",\n        \"id\": \"54c8b62d10f0ed1e048b4584\",\n        \"thumb\": {\n            \"id\": \"54c9193a90cc13ac048b4638png\",\n            \"width\": 25,\n            \"height\": 25,\n            \"url\": \"http://110.164.70.60/get/54c9193a90cc13ac048b4638png/\"\n        },\n        \"total_sniffer\": 0\n    },\n    {...}\n    ],\n    \"length\": 2\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event/today/:lang",
    "title": "GET /event/today/:lang",
    "description": "<p>Show an event from the future</p> ",
    "name": "GetEventToday",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "lang",
            "description": "<p>Language like en, th. Default is en</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"data\": [\n        {\n            \"name\": \"Title example\",\n            \"thumb\": {\n                \"id\": \"54ba7edc90cc137f238b45ffpng\",\n                \"width\": 100,\n                \"height\": 100,\n                \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba7edc90cc137f238b45ffpng\\/\"\n            },\n            \"id\": \"54ba1bc910f0edb8048b456c\",\n            \"category\": \"54c0ad7410f0ed5e048b4567\",\n            \"date_start\": \"2015-01-24 10:15:00\",\n            \"type\": \"item\",\n            \"total_sniffer\": 10\n        },\n        {...}\n    ],\n    \"length\": 2\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event/today/:lang",
    "title": "GET /event/today/:lang",
    "description": "<p>Show an event from the future</p> ",
    "name": "GetEventToday",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "lang",
            "description": "<p>Language like en, th. Default is en</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"data\": [\n        {\n            \"name\": \"Title example\",\n            \"thumb\": {\n                \"id\": \"54ba7edc90cc137f238b45ffpng\",\n                \"width\": 100,\n                \"height\": 100,\n                \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba7edc90cc137f238b45ffpng\\/\"\n            },\n            \"id\": \"54ba1bc910f0edb8048b456c\",\n            \"date_start\": \"2015-01-24 10:15:00\",\n            \"date_end\": \"2015-01-24 10:15:00\",\n            \"type\": \"item\",\n            \"total_sniffer\": 10\n        },\n        {...}\n    ],\n    \"length\": 2\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event/upcoming",
    "title": "GET /event/upcoming",
    "description": "<p>Show an upcoming event</p> ",
    "name": "GetEventUpcoming",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "limit",
            "description": "<p>[Optional] Limit event to display. Default is 20</p> "
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "/event/upcoming?limit=2",
          "type": "String"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"data\": [\n    {\n        \"date_start\": \"2015-02-04 17:13:01\",\n        \"detail\": \"test add detail 1422439981\",\n        \"name\": \"test add name 1422439981\",\n        \"id\": \"54c8b62d10f0ed1e048b4584\",\n        \"thumb\": {\n            \"id\": \"54c9193a90cc13ac048b4638png\",\n            \"width\": 25,\n            \"height\": 25,\n            \"url\": \"http://110.164.70.60/get/54c9193a90cc13ac048b4638png/\"\n        }\n        \"total_sniffer\": 2\n    },\n    {...}\n  ],\n  \"length\": 2\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "get",
    "url": "/event",
    "title": "GET /event",
    "description": "<p>Get all event</p> ",
    "name": "GetEvents",
    "group": "Event",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"length\": 1,\n    \"total\": 1,\n    \"data\": [\n        {\n            \"alarm\": 0,\n            \"approve\": 1,\n            \"build\": 1,\n            \"credit\": \"https:\\/\\/www.google.com\",\n            \"date_end\": \"1970-01-01 07:00:00\",\n            \"date_start\": \"1970-01-01 07:00:00\",\n            \"detail\": \"Example detail\",\n            \"name\": \"Example title\",\n            \"time_edit\": \"1970-01-01 07:00:00\",\n            \"time_stamp\": \"1970-01-01 07:00:00\",\n            \"user_id\": \"1\",\n            \"id\": \"54ba191510f0edb7048b456a\",\n            \"thumb\": {\n                \"id\": \"54ba7c3590cc13ab048b4628png\",\n                \"width\": 100,\n                \"height\": 100,\n                \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54ba7c3590cc13ab048b4628png\\/\"\n            }\n        },\n        { ... }\n    ],\n    \"paging\": {\n        \"page\": 1,\n        \"limit\": 15,\n        \"next\": \"http:\\/\\/eventsniff.dev\\/event?page=2\"\n    }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "post",
    "url": "/event/gallery/:event_id",
    "title": "POST /event/gallery/:event_id",
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
            "field": "event_id",
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
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"picture\": {\n        \"id\": \"54bf9d0b90cc13625e8b4577png\",\n        \"width\": 100,\n        \"height\": 100,\n        \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54bf9d0b90cc13625e8b4577png\\/\"\n    },\n    \"user_id\": \"1\",\n    \"event_id\": \"54b5e76510f0edc9068b4572\",\n    \"id\": \"54b5e76510f0edc9068b4572\",\n    \"status\": 200\n}",
          "type": "json"
        }
      ]
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
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"event_id\": \"54b5e76510f0edc9068b4572\",\n    \"user_id\": \"1\",\n    \"id\": \"54bf399610f0ed11048b456b\",\n    \"picture\": {\n        \"id\": \"54bf9ca890cc13aa048b4617png\",\n        \"width\": 100,\n        \"height\": 100,\n        \"url\": \"http:\\/\\/110.164.70.60\\/get\\/54bf9ca890cc13aa048b4617png\\/\"\n    },\n    \"status\": 200\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "put",
    "url": "/event/:event_id",
    "title": "PUT /event/:event_id",
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
            "field": "event_id",
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
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "location",
            "description": "<p>Lat Lng from google map</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "location_name",
            "description": "<p>Location name</p> "
          },
          {
            "group": "Parameter",
            "type": "Array",
            "optional": false,
            "field": "tags",
            "description": "<p>Category id E.g. [&#39;uj65tg&#39;, &#39;o8akuj&#39;, &#39;we8qw5&#39;]</p> "
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "lang",
            "description": "<p>Language like en, th. Default is en</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"name\": \"Example title\",\n    \"detail\": \"Example detail\",\n    \"date_start\": \"1970-01-01 07:00:00\",\n    \"date_end\": \"1970-01-01 07:00:00\",\n    \"credit\": \"https:\\/\\/www.google.com\",\n    \"time_edit\": \"1970-01-01 07:00:00\",\n    \"id\": \"54ba1bc910f0edb8048b456c\",\n    \"tags\": [\n        {\n            \"tag_id\": \"6f2da37e72bf9e100b40567c\",\n            \"name\": \"Promotion\"\n        },\n        {...},\n    ],\n    \"location\": {\n        \"name\": \"CNX\",\n        \"position\": \"19.906496, 99.834254\",\n    },\n    \"status\": 200\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/EventCTL.php",
    "groupTitle": "Event"
  },
  {
    "type": "put",
    "url": "/event/alarm/:event_id/:active",
    "title": "PUT /event/alarm/:event_id/:active",
    "description": "<p>Update alarm an event</p> ",
    "name": "PutEventAlarm",
    "group": "Event",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "event_id",
            "description": "<p>Event id</p> "
          },
          {
            "group": "Parameter",
            "type": "Integer",
            "optional": false,
            "field": "active",
            "description": "<p>0 is Disable, 1 is Enable</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"event_id\": \"54ba191510f0edb7048b456a\",\n    \"active\": 1\n}",
          "type": "json"
        }
      ]
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
    "url": "/sniff/category/:lang",
    "title": "GET /sniff/category/:lang",
    "description": "<p>Get all category</p> ",
    "name": "SniffCategory",
    "group": "Sniff",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "lang",
            "description": "<p>Language like en, th. Default is en</p> "
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n     \"length\": 20,\n     \"data\": [\n         {\n             \"id\": \"6f2da37e72bf9e100b40567c\",\n             \"name\" \"Awards\",\n         },\n         {\n             \"id\": \"e9c5c932c205770da433d3de\",\n             \"name\" \"Conferences\",\n         },\n         ...\n     ]\n}",
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
    "url": "/sniff/follow/:event_id/:user_id",
    "title": "POST /sniff/follow/:event_id/:user_id",
    "description": "<p>Follow an event</p> ",
    "name": "SniffFollow",
    "group": "Sniff",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "event_id",
            "description": "<p>Event id</p> "
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
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"event_id\":\"54ba191510f0edb7048b456a\",\n \"user_id\":\"54ba29c210f0edb8048b457a\",\n \"id\":\"54be2e6610f0ed53058b456b\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./private/src/Main/CTL/SniffCTL.php",
    "groupTitle": "Sniff"
  },
  {
    "type": "delete",
    "url": "/sniff/follow/:event_id/:user_id",
    "title": "DELETE /sniff/follow/:event_id/:user_id",
    "description": "<p>Unfollow an event</p> ",
    "name": "SniffUnfollow",
    "group": "Sniff",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"event_id\":\"54ba191510f0edb7048b456a\",\n \"user_id\":\"54ba29c210f0edb8048b457a\",\n}",
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