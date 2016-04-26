/*
require.config({
	paths: {
		jQuery: 'libs/lib-jquery',
		Underscore: 'libs/lib-underscore',
		Backbone: 'libs/lib-backbone',
		templates: '../templates'
	},
	waitSeconds: 20
});
require([
  'app',

  'order!libs/jquery-min',
  'order!libs/underscore-min',
  'order!libs/backbone-min'
], function(App){
  App.initialize();
});
*/


var _myRjsConfigObj = {
	paths: {
		jQuery2: 'libs/jquery-min',
		Underscore: 'libs/underscore-min',
		Backbone: 'libs/backbone',
		templates: '../templates',
		jQuery: 'libs/jquery-ajax'
	},
	shim: {
		jQuery2: {
			exports: 'jQuery2'
		},
		Underscore: {
		  exports: '_'
		},
		Backbone: {
		  deps: ["jQuery", "Underscore"],
		  exports: "Backbone"
		},
		jQuery: {
		  deps: ["jQuery2", "Underscore"],
		  exports: "$"
		}
	},
	waitSeconds: 20
};
if (window._isOldIE){
	_myRjsConfigObj.paths.jQuery = 'libs/lib-jquery';
	delete _myRjsConfigObj.shim.jQuery;
}
if (!Date.prototype.toISOString) { 
	Date.prototype.toISOString = function() { function pad(n) { return n <10 ? '0' + n : n } return this.getUTCFullYear() + '-' + pad(this.getUTCMonth() + 1) + '-' + pad(this.getUTCDate()) + 'T' + pad(this.getUTCHours()) + ':' + pad(this.getUTCMinutes()) + ':' + pad(this.getUTCSeconds()) + '.' + pad(this.getUTCMilliseconds()) + 'Z'; } 
}

require.config(_myRjsConfigObj);

require([
  'app',
  'jQuery',
  'Underscore',
  'Backbone'
], function(App, $, _, Backbone){
	
	window.console = window.console||{log:function(){}};
	
	if (window._isOldIE || 'msTransition' in document.body.style){
		console.log('[main] is IE.');
		
		var emit = function(lk){ //fix IE Anchor Link which has html content
		  if ('_appFacade' in window && !!window._appFacade.router){
					var idx = lk.href.indexOf('#');
					var h = lk.href.substr(idx);
					location.hash = h;
				}
		};
		if (window._isOldIE){
		  $(document).on('click', 'a', function(e){
			  var domain1 = location.href.split('#')[0];
			  var href1 = e.currentTarget.href;
			  var hash1 = href1.replace(domain1, '');          
				  if ( /^\#/.test(hash1) || /^\#/.test(href1) ){
				emit(e.currentTarget);                      
			  }                                            
			}); 
		}else{
		  $(document).on('click', 'a[href^=#]', function(e){ //fix IE Anchor Link 
				  emit(e.currentTarget);                                                                  
			});     
		}
		
	}else{
		console.log('[main] not IE.');
	}
	
	_.noConflict();
	$.noConflict();
	Backbone.noConflict();
	
	App.initialize();
});
