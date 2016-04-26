define([ 
  'Underscore', 
  'Backbone',
  'models/BaseModel'
], function(_, Backbone, BaseModel){
	
	var CheckinDaily = BaseModel.extend({
		urlRoot: "/checkin/daily"
	});
	
	return CheckinDaily;
});
