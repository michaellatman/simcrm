
/*
 * GET users listing.
 */
var mongoose = require( 'mongoose' );
var Todo     = mongoose.model( 'Todo' )
, Companies     = mongoose.model( 'Companies' )
, utils    = require( 'connect' ).utils;


exports.handle = function(req, res){
	//if(mongoose == null)
	switch (req.params.method)
	{
		case 'getperson':
			Companies.remove({}, function () { 
				new Companies({
			       nickname   : "NICKNAME",
				   users      : ["519fff4b2d7e307a4b000003","519fff4b2d7e307a4b000003","519fff4b2d7e307a4b000003"],
				   managers   : ["519fff4b2d7e307a4b000003"],
				   lead       : "519fff4b2d7e307a4b000003"
			  	}).save( function ( err, todo, count ){
			    	if( err ) return next( err );
			    	Companies.findOne({ 'nickname': 'NICKNAME' }, 'users').populate({path: 'users',
  match: { content: "HAHA i'm linked " }}).exec(function (err, content) {
					 	if (err) return handleError(err);
						if(content.users == 0)
							console.log('error');
						else
						res.send(content.users[0].content);
					});
			  	});
		  	});
			break;
		default:
		  	var objToJson = {status:"failure",message:"Unknown handle"};
		  	var response;
			objToJson.response = response;
			res.send(JSON.stringify(objToJson));
			break;
	}
};