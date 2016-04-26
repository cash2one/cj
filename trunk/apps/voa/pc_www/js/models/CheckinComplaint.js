define([ 
  'Underscore', 
  'Backbone',
  'models/BaseModel',
  
  'utils/appUtils'
], function(_, Backbone, BaseModel, appUtils){
	
	
	
	var CheckinComplaint = BaseModel.extend({
		urlRoot: "/checkin/complaint",
		
		validate: function(attrs){
			if ( !appUtils.string.trim(attrs.subject.value).length ) {
				return 'empty_subject';
			}
			else if ( !appUtils.string.trim(attrs.content.value).length ) {
				return 'empty_content';
			}
		}
	});
	
	return CheckinComplaint;
});
