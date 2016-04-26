define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'models/CheckinDaily',
  'text!templates/checkin/daily.html'
], function($, _, Backbone, appUtils, DailyModel, theTmpl){

  var CheckinDailyView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'checkin_daily',
	
	template: _.template(theTmpl),
	
	collection: null,
	tmplData: null,
	
	initialize: function(){
	},
	
	render: function(){
		
		appUtils.doSyncGet('/checkin/daily',  _.bind(this.onGet, this));
		
		return this;
    },
	
	events: {
		"mousewheel .body": "showScrollbar",
		"mouseover .body": "showScrollbar",
		"mouseout .body": "hideScrollbar",
		"click .checkBtn":	"onBtnClick"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	applyScrollbar: function(){
		var outHt = this.el.parentNode.clientHeight;
		var innerHt = outHt - this.$el.find('.header').height() - 32/*paddingTop*/ - 1;
		appUtils.applyCustomScrollbar(
			this.$el,
			this.$el.find('.body'),
			outHt,
			innerHt
		);
	},
	
	onGet: function(data, ret){
		//fill data
		var rst = appUtils.parseAjax(ret);
		if (!rst) return;
		var tdata = _.extend(rst, {});
		
		//model
		this.collection = new Backbone.Collection;
		for (var i=0, lng=tdata.list.length; i<lng; i++){
			var m = new DailyModel(tdata.list[i]);
			m.on('change', this.onItemChange, this);
			this.collection.add(m);
		}
		
		this.layout(tdata, false);
		this.tmplData = tdata;
	},
	
	onItemChange: function(model){
		this.layout(this.tmplData, true);
	},
	
	layout: function(tdata, update){
		if (update){
			tdata.list = this.collection.map(function(m){
				return m.toJSON();
			});
		}
	
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col3.html(this.el);
		
		//layout scrollbar
		this.applyScrollbar();
		
		//bind event
		/*var _view = this;
		$('.checkBtn', this.$el).each(function(idx, ele){
			ele.onclick = function(e){
				var idx = $(this.parentNode).index();
				var model = _view.collection.at(idx);
				model.save({wait: true});
			}
		});*/
		this.delegateEvents();
	},
	
	onBtnClick: function(e){
		var idx = $(e.currentTarget).parent().index();
		var model = this.collection.at(idx);
		model.save({wait: true});
	}
	
  });
  return CheckinDailyView;
});
