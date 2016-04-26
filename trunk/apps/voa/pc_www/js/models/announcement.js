define([ 
  'Underscore', 
  'Backbone',
  'models/BaseModel'
], function(_, Backbone, BaseModel){
	
	var Announcement = BaseModel.extend({
		urlRoot: "/announcement"
	});
	
	return Announcement;
});
