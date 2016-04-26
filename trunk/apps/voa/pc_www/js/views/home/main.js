define([
  'jQuery',
  'Underscore',
  'Backbone',
  'text!templates/home/main.html',
  'views/home/leftmenu'
], function($, _, Backbone, mainHomeTemplate, leftmenuView){
  var mainHomeView = Backbone.View.extend({
    el: "#app_container",
    
	render: function(data) {
    	
		this.$el.html(mainHomeTemplate);
		
		window._appFacade.layout.$col1 = $('#main_col1');
		window._appFacade.layout.$col2 = $('#main_col2');
		window._appFacade.layout.$col3 = $('#main_col3');
		
		window._appFacade.layout.$col1.append( leftmenuView.render().el );
		
		//fix ie6
		var cHeight = $(window).height() - 18; //window.innerHeight - 18;
		
		window._appFacade.layout.$col1.height(cHeight);
		window._appFacade.layout.$col2.height(cHeight);
		window._appFacade.layout.$col3.height(cHeight);
		
		if (window._isOldIE){
			this.$el.height(cHeight);
		}
	
		window._oa_pc_app_layout_ok = true;
    },
	
	reRender: function(data) {
		
		if (!window._oa_pc_app_layout_ok){
			this.render();
		}
		window._appFacade.layout.$col2.html('');
		window._appFacade.layout.$col3.html('');
		
		if ('leftmenuAction' in data){
			leftmenuView.currentAction = data.leftmenuAction;
		}
	}
	
  });
  return new mainHomeView;
});
