define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'models/leftmenu',
  'text!templates/home/leftmenu.html'
], function($, _, Backbone, appUtils,
	menuModel, menuTemplate){

	var menuView = Backbone.View.extend({
    
	tagName: 'div',
	id: "app_leftmenu",
    
	model: menuModel,
	
	template: _.template(menuTemplate),
	
	currentAction: null, //用于直接从#xxx进入页面时
	itemsByName: {},
	
	initialize: function() {
	
	},
	
	render: function(){
		
		var _view = this;
		
		//TODO loading...
		this.model.fetch({
			success: function(model, data){
				//TODO loading...
				
				//console.log(123, model.toJSON(), data);
				var rst = appUtils.parseAjax(data);
				if (!rst) return;
				
				var tdata = _.extend(rst, {
					user: window._appFacade.appData.user,
					copyright: window._appFacade.appData.copyright
				});
				_view.$el.html( _view.template(tdata) );
				
				//action cache
				$('.panel .menu ul>li>a', _view.$el).each(function(idx, ele){
					var hIdx = ele.href.indexOf('#');
					var actName = ele.href.substr(hIdx+1);
					_view.itemsByName[ actName ] = ele;
				});
				if (_view.currentAction){
					_view.clickItem(_view.currentAction);
				}
				
				//layout copyright
				var $copy = $('.copy', _view.$el);
				var cptop = 20;
				if ( $copy.offset().top < $(document).height() - $copy.height() ) {
					var nagativeValue = $copy.offset().top - ($(document).height() - $copy.height());
					nagativeValue += cptop;
					$copy.css({
						'position': 'relative',
						'bottom': nagativeValue
					});
				}
				
				//layout scrollbar
				var panelHt = _view.$el.parent().height() - 106;
				var menuHt = panelHt - 62 - 9;
				appUtils.applyCustomScrollbar(
					_view.$el.find('.panel'),
					_view.$el.find('.menu'),
					panelHt,
					menuHt
				);
			}
		});
	  
		return this;
    },
	
	events: {
		"mousewheel .menu": "showScrollbar",
		"mouseover .menu": "showScrollbar",
		"mouseout .menu": "hideScrollbar",
		"click .panel .menu ul>li>a": "clickItem"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	
	clickItem: function(e){
		var mode = typeof e === 'string'
						? 'route'
						: 'manual';
						
		var item = mode == 'route'
						? this.itemsByName[e]
						: e.currentTarget;
		$('.panel .menu ul>li>a').removeClass('current');
		$(item).addClass('current');
		
		if (mode == 'manual'){ //去掉未读提示数
			var $ur = $('.unread', item);
			if ($ur.length){
				
				var attr = 'unread';
				attr = $ur.parent().find('label').text() + '.' + attr;
				attr = 'list' + '.' + attr;
				attr = $ur.parents('ul').prev('h2').text() + '.' + attr;
				attr = 'menu' + '.' + attr;
				
				this.model.updateAttr(attr, 0, {
					success: function(data){
						console.log("[leftmenu] 删除已读数，成功：", data.errmsg);
						$ur.remove();
					},
					error: function(data){
						console.log("[leftmenu] 删除已读数，失败：", data.errmsg);
					}
				});
			}
		}
	}
	
  });
  
  return new menuView;
});