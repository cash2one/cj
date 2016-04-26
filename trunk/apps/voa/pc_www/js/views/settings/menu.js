define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'text!templates/settings/menu.html'
], function($, _, Backbone, appUtils, theTmpl){

  var SettingsMenuView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'settings_menu',
	
	template: _.template(theTmpl),
	
	initialize: function(){
		this.currAct = window._appFacade.router.current();
	},
	
	render: function(){
		
		appUtils.doSyncGet('/settings/menu',  _.bind(this.onGet, this));
		
		return this;
    },
	
	events: {
		"mousewheel .listContainer": "showScrollbar",
		"mouseover .listContainer": "showScrollbar",
		"mouseout .listContainer": "hideScrollbar",
		"click li>a": "clickItem"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	applyScrollbar: function(){
		var outHt = this.el.parentNode.clientHeight;
		var innerHt = outHt - this.$el.find('h1').height();
		appUtils.applyCustomScrollbar(
			this.$el,
			this.$el.find('.listContainer'),
			outHt,
			innerHt
		);
	},
	
	onGet: function(data, ret){
		//fill data
		var rst = appUtils.parseAjax(ret);
		if (!rst) return;
		var tdata = _.extend(rst, {});
		
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col2.html(this.el);
		
		switch (this.currAct.name){
			case 'settingsMenuAction':
				window._appFacade.layout.$col3.html('');
			break;
		}
		
		this.highlightCurrent();
		
		//layout scrollbar
		this.applyScrollbar();
		
		//console.log('[CheckinMenuView] onGet');
	},
	
	highlightCurrent: function(){
		var f = this.currAct.fragment;
		$('.listContainer ul li').each(function(idx, ele){
			$(ele).removeClass('current');
			var h = $('.m_link', ele).attr('href').replace(/^\#/, '');
			if (f.indexOf(h) == 0){
				$(ele).addClass('current');
			}
		});
	},
	
	clickItem: function(e){ //直接渲染 避免页面整体重建
		e.preventDefault();
		e.stopPropagation();
		
		var action = e.currentTarget.href.split('/').pop();
		_appFacade.router.settingsPagesAction(action, false);
		window._appFacade.router.navigate('settings/' + action, {trigger: false});
		
		$(e.currentTarget).parent().parent().find('li').removeClass('current');
		$(e.currentTarget).parent().addClass('current');
	}
	
  });
  return SettingsMenuView;
});
