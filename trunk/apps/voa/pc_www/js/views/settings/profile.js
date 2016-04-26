define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'models/SettingsProfile',
  'text!templates/settings/profile.html'
], function($, _, Backbone, appUtils, 
	theModel, theTemplate){
  var SettingProfileView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'settings_profile',
	
	model: null,
	template: _.template(theTemplate),
	
	render: function(){
		var _view = this;
		
		//TODO loading...
		(this.model = new theModel()).fetch({
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
  return SettingProfileView;
});
