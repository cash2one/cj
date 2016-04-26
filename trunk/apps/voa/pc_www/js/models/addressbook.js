define([ 
  'Underscore', 
  'Backbone',
  'models/BaseModel'
], function(_, Backbone, BaseModel){
	
	var Addressbook = BaseModel.extend({
		urlRoot: "/addressbook"
	});
	
	return Addressbook;
});
