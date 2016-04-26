define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'collections/announcement',
  'models/announcement',
  'text!templates/announcement/list.html',
  
  'views/announcement/detail'
], function($, _, Backbone, appUtils,
	TheList, TheModel, theTmpl,
	AnnodetailView){
  var AnnouncementListView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'announcement_list',
	
	collection: null,
	template: _.template(theTmpl),
	
	initialize: function(){
		this.collection = new TheList(/*{model: TheModel}*/);
		this.collection.bind('reset', this.onReset, this);
		
		this.currAct = window._appFacade.router.current();
	},
	
	render: function(){
		this.collection.fetch({reset: true});
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
	
	onReset: function(collection, sync){
		//fill data
		var rst = appUtils.parseAjax(sync.xhr.responseJSON);
		if (!rst) return;
		var tdata = _.extend(rst, {});
		
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col2.html(this.el);
		
		switch (this.currAct.name){
			case 'announcementListAction':
				window._appFacade.layout.$col3.html('');
			break;
		}
		
		//layout scrollbar
		this.applyScrollbar();
		
		this.delegateEvents();
	},
	
	clickItem: function(e){ //直接渲染 避免页面整体重建
		e.preventDefault();
		e.stopPropagation();
		
		var fragment = e.currentTarget.href.split('#')[1];
		var id = _appFacade.router.current(fragment).params[0];
		(new AnnodetailView).render(id);
		window._appFacade.router.navigate('announcement/' + id, {trigger: false});
		//console.log('[AnnouncementListView] show detail: ', id);
		
		$(e.currentTarget).parent().parent().find('li').removeClass('current');
		$(e.currentTarget).parent().addClass('current');
	}
	
  });
  return AnnouncementListView;
});
