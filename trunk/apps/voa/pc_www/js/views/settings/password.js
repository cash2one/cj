define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'text!templates/settings/password.html'
], function($, _, Backbone, appUtils, theTemplate){
  var SettingProfileView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'settings_password',
	
	model: null,
	template: _.template(theTemplate),
	
	render: function(){
	
		var url = '/settings/password';
		appUtils.doSyncGet(url,  _.bind(this.onGet, this));
	  
		return this;
    },
	
	events: {
		"mousewheel .body": "showScrollbar",
		"mouseover .body": "showScrollbar",
		"mouseout .body": "hideScrollbar",
		"submit form": "onSubmit"
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
	},
	
	onGet: function(data, ret){
		//fill data
		var rst = appUtils.parseAjax(ret);
		if (!rst) return;
		var tdata = _.extend(rst, {});
		
		this.tmplData = tdata;
		
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col3.html(this.el);
		
		
		
		//layout scrollbar
		this.applyScrollbar();
		
		appUtils.parsePlaceholder();
		
		this.delegateEvents();
	},
	
	onSubmit: function(e){
		e.preventDefault();
		
		var _view = this;
		
		function _showIcon($ipt, type){
			var $i = $ipt.next();
			$i.get(0).className = '';
			$i.addClass(type);
		}
		
		//格式
		var arr = ['old','new','re'];
		for (var i=0,lng=arr.length; i<lng; i++){
			var n = arr[i];
			var $ipt = $('input[type=password].'+n, _view.el);
			_showIcon($ipt, '');
			var re = new RegExp(_view.tmplData.pattern);
			var v = $ipt.val()||"";
			if ( !re.test(v) ){
				appUtils.dialog.warning(_view.tmplData.label[n] + ": " 
					+ _view.tmplData.validNotice.invalid);
				_showIcon($ipt, 'fail');
				return false;
			}
		};
		
		//一致
		var $np1 = $('input[type=password].new', this.el);
		var $np2 = $('input[type=password].re', this.el);
		if ($np1.val()!==$np2.val()){
			appUtils.dialog.warning(this.tmplData.validNotice.different);
			_showIcon($np1, 'fail');
			_showIcon($np2, 'fail');
			return false;
		}
		
		_showIcon($('input[type=password].old'), 'succ');
		_showIcon($np1, 'succ');
		_showIcon($np2, 'succ');
		//确认
		var $op = $('input[type=password].old', this.el);
		var cfm = this.tmplData.buttons.submit.confirm;
		appUtils.dialog.confirm(
			cfm.message,
			cfm.no,
			cfm.yes,
			function(nofy){				
				nofy.close();
			},
			function(nofy){
			
				var pdata = {
					'old': $op.val(),
					'new': $np1.val(),
					're': $np2.val()
				};
				$.post('/settings/password', pdata, function(json){
					var j = json.result;
					if (!!j.success){
						window._appFacade.router.navigate('settings', {trigger:true});
						appUtils.dialog.success(j.message);
					}else{
						appUtils.dialog.error(j.message);
					}
				}, 'json');
				
				nofy.close();
			}
		);
		
		return false;
	}
	
  });
  return SettingProfileView;
});
