var http = require('http');
var url  = require('url');
var querystring = require('querystring');
var redis = require('redis');

var mongodbClient = require('mongodb').MongoClient;

var server = http.createServer();
server.on( 'request', function( request, response ) {
  if ( 'POST' == request.method ) {
    var ret;
    var token = request.headers['token'];
    if ( '' == token || undefined == token || null == token ) {
      ret = {code:-1,message:"无效token"};
      response.writeHead( 200, { "Content-Type":"application/json" } ); 
      response.end( JSON.stringify( ret ) );
      return;
    }
    // 从redis利用token读取出session
    var session;
    redisClient = redis.createClient( '6379', '127.0.0.1' ); 
    redisClient.on( 'ready', function( err ) {
      redisClient.hgetall( 'token:'+token, function( err, session ) {
        if ( null == session ) {
          ret = {code:-1,message:"无效token"};
          response.writeHead( 200, { "Content-Type":"application/json" } ); 
          response.end( JSON.stringify( ret ) );
          return;
        }
        if ( typeof session.uid == 'undefined') {
          ret = {code:-1,message:"无效token"};
          response.writeHead( 200, { "Content-Type":"application/json" } ); 
          response.end( JSON.stringify( ret ) );
          return;
        }
        // 开始业务逻辑.. 
				var postData = ""; 
				// 数据块接收中
				request.addListener( "data", function ( postDataChunk ) {
					postData += postDataChunk;
				} );
				// 数据接收完毕，执行回调函数
				request.addListener( "end", function () {
					var params = querystring.parse( postData );
					var lat = params.lat;
					var lng = params.lng;
					var mongoUrl = "mongodb://127.0.0.1:27017/";
					mongodbClient.connect( mongoUrl, { useNewUrlParser: true, useUnifiedTopology: true }, function( err, db ) {
						if ( err ) throw err;
						var dbo   = db.db('momo');
						dbo.collection('user').find(
							{ 'loc':
								{ $near :
									{ $geometry:
										{ type: "Point",  coordinates: [ parseFloat(lng), parseFloat(lat) ] },
										$maxDistance: 99999999,
									}
								}
							} 
						).limit( 20 ).toArray( function( err, result ) {
							var uids = [];
							result.forEach( function( key, index, arr ){
                console.log( key );
								uids.push( key._id );
							} );
							var ret = { code:0,data:uids }; 
							response.writeHead( 200, { "Content-Type":"application/json" } ); 
							response.end( JSON.stringify( ret ) );
						} );
						db.close();
					} ); 
				} );

      } );
    } );
  }

} )
server.listen( 8000, '0.0.0.0' );
console.log( "server@0.0.0.0:8000" );
