define([ 
  'Underscore', 
  'Backbone',
  'models/BaseModel'
], function(_, Backbone, BaseModel){
	
	var SettingsProfile = BaseModel.extend({
		urlRoot: "/settings/profile"
	});
	
	return SettingsProfile;
});
