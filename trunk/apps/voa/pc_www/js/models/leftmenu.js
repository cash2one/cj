define([ 
  'Underscore', 
  'Backbone',
  'models/BaseModel'
], function(_, Backbone, BaseModel){
	
	var LeftMenu = BaseModel.extend({
		urlRoot: "/leftmenu"
	});
	
	return new LeftMenu;
});
