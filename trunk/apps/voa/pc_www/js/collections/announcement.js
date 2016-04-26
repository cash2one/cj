define([
  'jQuery', 
  'Underscore', 
  'Backbone',
  'collections/BaseCollection'
], function($, _, Backbone, BaseCollection){

	var AnnouncementCollection = BaseCollection.extend({
		urlBase: '/announcement/search'
	});
	
  return AnnouncementCollection;
});