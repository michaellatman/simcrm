var mongoose = require( 'mongoose' );
var Schema   = mongoose.Schema;
 
var Todo = new Schema({
    user_id    : String,
    content    : String,
    updated_at : Date
});

var Companies = new Schema({
    nickname   : String,
    users      : [{ type: Schema.Types.ObjectId, ref: 'Todo' }],
    managers   : [],
    lead       : Number
});
 
mongoose.model( 'Todo', Todo );

mongoose.model( 'Companies', Companies );
 
mongoose.connect( 'mongodb://mrl4214:j7weWred@dharma.mongohq.com:10072/BetterSimCRM' );