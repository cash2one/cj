define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'views/home/main',
  'views/addressbook/list',
  'views/addressbook/detail',
  'views/announcement/list',
  'views/announcement/detail',
  'views/checkin/menu',
  'views/checkin/daily',
  'views/checkin/calendar',
  'views/checkin/complaint',
  'views/settings/menu',
  'views/settings/profile',
  'views/settings/password'
], function($, _, Backbone, appUtils,
	mainLayoutView, 
	AddrlistView, AddrdetailView,
	AnnolistView, AnnodetailView,
	ChkinmenuView, ChkindailyView, ChkincalenView, ChkincpltView,
	SettingsmenuView, SettingsProfileView, SettingsPwdView){

	function _init(viewOptions, callback){
	
		if ('appData' in window._appFacade && !!window._appFacade.appData){
			_initHelper(viewOptions, callback);
		}else{
			//TODO loading
			appUtils.doSyncGet('/init', function(data){
				//TODO loading
				window._appFacade.appData = _.extend(data, {});
				_initHelper(viewOptions, callback);
				console.log('[router] initialze data loaded', window._appFacade.appData);
			});
		}
	}
	function _initHelper(viewOptions, callback){
		mainLayoutView.reRender(viewOptions||{});
		if (callback) callback.call(null);
	}
	
  var AppRouter = Backbone.Router.extend({
	routes: {
      //URL routes
      'addressbook(/)':							'addressbookListAction',
      'addressbook/search/:page(/)(:query)':	'addressbookSearchAction',
      'addressbook/:id':						'addressbookDetailAction',
	  'announcement(/)':						'announcementListAction',
	  'announcement/:id':						'announcementDetailAction',
	  'checkin(/)':								'checkinMenuAction',
	  'checkin/*action':						'checkinPagesAction',
	  'settings(/)':							'settingsMenuAction',
	  'settings/*action':						'settingsPagesAction',
      //Default
	  'error/:code/:msg':						'errorAction',
      '(*actions)':								'defaultAction'
    },
	current: function(p_fragment) { //get current route info
	  var fragment = p_fragment || Backbone.history.fragment,
		  routes = _.pairs(this.routes),
		  route,
		  name,
		  found;
	  found = _.find(routes, function (namedRoute) {
		route = namedRoute[0];
		name = namedRoute[1];
		if (!_.isRegExp(route)) {
		  route = this._routeToRegExp(route);
		}
		return route.test(fragment);
	  }, this);
	  if (found) {
		return {
		  name: name,
		  params: this._extractParameters(route, fragment),
		  fragment: fragment
		};
	  }
	},
	
	//通讯录列表
	addressbookListAction: function(){
		_init({
			'leftmenuAction': 'addressbook'
		}, function(){
			(new AddrlistView).render();
		});
		this.showRouteLog();
	},
	//通讯录搜索
	addressbookSearchAction: function(page, query){
		_init({
			'leftmenuAction': 'addressbook'
		}, function(){
			(new AddrlistView).render(page, query);
		});
		this.showRouteLog();
	},
	//通讯录某人详情
	addressbookDetailAction: function(id){
		_init({
			'leftmenuAction': 'addressbook'
		}, function(){
			(new AddrlistView).render();
			(new AddrdetailView).render(id);
		});
		this.showRouteLog();
	},
	//公告列表
	announcementListAction: function(){
		_init({
			'leftmenuAction': 'announcement'
		}, function(){
			(new AnnolistView).render();
		});
		this.showRouteLog();
	},
	//公告详情
	announcementDetailAction: function(id){
		_init({
			'leftmenuAction': 'announcement'
		}, function(){
			(new AnnolistView).render();
			(new AnnodetailView).render(id);
		});
		this.showRouteLog();
	},
	//签到菜单
	checkinMenuAction: function(){
		_init({
			'leftmenuAction': 'checkin'
		}, function(){
			(new ChkinmenuView).render();
		});
		this.showRouteLog();
	},
	//签到各功能页
	checkinPagesAction: function(action, renderMenu){
		
		if (renderMenu === null || typeof renderMenu === 'undefined') renderMenu = true;
		
		var doSwitch = function(action){
			switch(action){
				case 'daily':
					(new ChkindailyView).render();
					break;
				case 'calendar':
					(new ChkincalenView).render();
					break;
			}
			if ( /^calendar\/\d{4}\/\d{1,2}$/.test(action) ){
				var m = action.match(/^calendar\/(\d{4})\/(\d{1,2})$/);
				var year = m[1];
				var month = m[2];
				(new ChkincalenView).render(year, month);
			}else if( /^complaint\/\d{4}\/\d{1,2}$/.test(action) ){
				var m = action.match(/^complaint\/(\d{4})\/(\d{1,2})$/);
				var year = m[1];
				var month = m[2];
				(new ChkincpltView).render(year, month);
			}
		};
		
		if (renderMenu){
			_init({
				'leftmenuAction': 'checkin'
			}, function(){
				(new ChkinmenuView).render();
				doSwitch(action);
			});
			this.showRouteLog();
		}else{
			doSwitch(action);
		}
	},
	//设置
	settingsMenuAction: function(){
		_init({
			'leftmenuAction': 'settings'
		}, function(){
			(new SettingsmenuView).render();
		});
		this.showRouteLog();
	},
	//设置各功能页
	settingsPagesAction: function(action, renderMenu){
		if (renderMenu === null || typeof renderMenu === 'undefined') renderMenu = true;
		
		var doSwitch = function(action){
			switch(action){
				case 'profile':
					(new SettingsProfileView).render();
					break;
				case 'password':
					(new SettingsPwdView).render();
					break;
			}
		};
		
		if (renderMenu){
			_init({
				'leftmenuAction': 'settings'
			}, function(){
				(new SettingsmenuView).render();
				doSwitch(action);
			});
			this.showRouteLog();
		}else{
			doSwitch(action);
		}
	},
	//默认
    defaultAction: function(actions){
		_init();
		this.showRouteLog();
    },
	//错误
	errorAction: function(code, msg){
		console.log('[router] ==error==', code, msg);
		appUtils.dialog.error(['[router error]', code, msg].join('::'));
		this.showRouteLog();
	},
	
	showRouteLog: function(){
		console.log("[router] ", this.current() );
	}
  });

  var initialize = function(){
  
		window._appFacade = {
			layout: {},
			appData: null,
			router: new AppRouter
		};
		Backbone.history.start();
  };
  return { 
    initialize: initialize
  };
});
