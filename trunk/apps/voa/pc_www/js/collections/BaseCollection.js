define([
  'Underscore', 
  'Backbone',
], function(_, Backbone){
	
	var C = Backbone.Collection.extend({
		
		urlBase: '',
		page: 0,
		query: '',
		 
		initialize: function(models, options) {
			var _ref = this;
			
			this.add(new Backbone.Model({id:111, name:'xxx'}));
			
			this.url = function(){
					return [_ref.urlBase, _ref.page, _ref.query].join('/')
						.replace(/\/+/, '/');
						//.replace(/\/$/, '');
				};
			this.parse = function(resp, xhr){
					var rst = resp.result;
					if ('list' in rst){
						_ref.others = _.clone(rst);
						delete _ref.others.list;
						return rst.list;
					}
					return rst;
				};
		},
		
		others: {}
		
	});
	
	return C;
});
