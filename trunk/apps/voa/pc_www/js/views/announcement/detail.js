define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'models/announcement',
  'text!templates/announcement/detail.html'
], function($, _, Backbone, appUtils,
	BookModel, bookDetailTemplate){
  var AnnouncementDetailView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'announcement_detail',
	
	model: null,
	template: _.template(bookDetailTemplate),
	
	render: function(id){
		var _view = this;
		
		//TODO loading...
		(this.model = new BookModel({id: id})).fetch({
			success: function(model, data, sync){
				//TODO loading...
				
				//fill data
				var rst = appUtils.parseAjax(data);
				if (!rst) return;
				
				//build dom
				_view.$el.html( _view.template(rst) );
				window._appFacade.layout.$col3.html(_view.el);
				
				//layout scrollbar
				_view.applyScrollbar.call(_view);
			}
		});
	  
		return this;
    },
	
	events: {
		"mousewheel .body": "showScrollbar",
		"mouseover .body": "showScrollbar",
		"mouseout .body": "hideScrollbar"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	
	applyScrollbar: function(){
		var outHt = this.el.parentNode.clientHeight;
		var innerHt = outHt - this.$el.find('.header').height() - 1;
		appUtils.applyCustomScrollbar(
			this.$el,
			this.$el.find('.body'),
			outHt,
			innerHt
		);
	}
	
  });
  return AnnouncementDetailView;
});
