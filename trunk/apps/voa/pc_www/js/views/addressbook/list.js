define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'collections/addressbook',
  'models/addressbook',
  'text!templates/addressbook/list.html',
  
  'views/addressbook/detail'
], function($, _, Backbone, appUtils,
	BookList, BookModel, bookListTemplate,
	AddrdetailView){
  var AddressbookListView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'addressbook_list',
	
	collection: null,
	template: _.template(bookListTemplate),
	
	initialize: function(){
		this.collection = new BookList(/*{model: BookModel}*/);
		this.collection.bind('reset', this.onReset, this);
		
		this.currAct = window._appFacade.router.current();
	},
	
	render: function(page, query){
		if (typeof page === 'undefined') page = 0;
		if (typeof query === 'undefined') query = '';
		this.collection.page = page;
		this.collection.query = query;
		this.collection.fetch({reset: true});
		return this;
    },
	
	events: {
		"mousewheel .listContainer": "showScrollbar",
		"mouseover .listContainer": "showScrollbar",
		"mouseout .listContainer": "hideScrollbar",
		"click dt": "stretchList",
		"click .sch .btn": "doSearch",
		"click dd>ul>li>a": "clickItem"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	
	applyScrollbar: function(){
		var outHt = this.el.parentNode.clientHeight;
		var innerHt = outHt - this.$el.find('h1').height() - this.$el.find('.sch').height();
		appUtils.applyCustomScrollbar(
			this.$el,
			this.$el.find('.listContainer'),
			outHt,
			innerHt
		);
	},
	
	stretchList: function(e) { //伸展列表
		var index = 0;
		
		if (typeof e === 'undefined') {
			switch (this.currAct.name){
				case 'addressbookDetailAction':
					var id = this.currAct.params[0],
						li = $('#ablst_'+id),
						dt = li.parent().parent().prev();
					index = dt.index('.listContainer dt');
					li = null;
					dt = null;
					break;
				case 'addressbookListAction':
				case 'addressbookSearchAction':
				default:
					index = 0;
					break;
			}
		}else{
			index = $(e.currentTarget).index('.listContainer dt');
		}
		
		$('dd ul', this.el).each(function(idx, ele){
			var $arrow = $('i', $(ele).parent().prev());
			if (idx === index){
				$(ele).show();
				$arrow.addClass('opened');
			}
			else{
				$(ele).hide();
				$arrow.removeClass('opened');
			}
			$arrow = null;
		});
		this.applyScrollbar();
	},
	
	onReset: function(collection, sync){
		//fill data
		var rst = appUtils.parseAjax(sync.xhr.responseJSON);
		if (!rst) return;
		var tdata = _.extend(rst, {
			search: window._appFacade.appData.fragments.addressbook.search
		});
		var grps = {};
		$(tdata.groups).each(function(idx, ele){
			var models = [].concat( collection.where({departmentid:ele.departmentid}) );
			if (models.length){
				grps[ele.name] = $(models).map(function(i, model){
								return model.toJSON();
							});
			}
			models = null;
		});
		tdata.groups = grps;
		delete tdata.list;
		
		console.log(tdata.groups);
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col2.html(this.el);
		
		switch (this.currAct.name){
			case 'addressbookListAction':
			case 'addressbookSearchAction':
				window._appFacade.layout.$col3.html('');
			break;
		}
		
		//layout scrollbar
		this.applyScrollbar();
		
		//layout stretch
		this.stretchList();
		
		this.delegateEvents();
	},
	
	doSearch: function(e){
		var vlu = $('input', this.$el.find('.sch')).val();
		if (!vlu.length) return;
		window._appFacade.router.navigate('addressbook/search/0/' + vlu, {trigger: true});
	},
	
	clickItem: function(e){ //直接渲染 避免页面整体重建
		e.preventDefault();
		e.stopPropagation();
		
		var fragment = e.currentTarget.href.split('#')[1];
		var id = _appFacade.router.current(fragment).params[0];
		(new AddrdetailView).render(id);
		window._appFacade.router.navigate('addressbook/' + id, {trigger: false});
		
		$(e.currentTarget).parent().parent().find('li').removeClass('current');
		$(e.currentTarget).parent().addClass('current');
	}
	
  });
  return AddressbookListView;
});
