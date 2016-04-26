define([
  'jQuery', 
  'Underscore', 
  'Backbone',
  'collections/BaseCollection'
], function($, _, Backbone, BaseCollection){

	var AddressbookCollection = BaseCollection.extend({
		urlBase: '/addressbook/search'
	});
	
  return AddressbookCollection;
});