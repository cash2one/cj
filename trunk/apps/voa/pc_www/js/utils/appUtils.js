define([
  'Underscore',
  'Backbone',
  
  'libs/jquery.tinyscrollbar.min',
  'libs/jquery.noty.packaged.min'
  //'order!libs/jquery.tinyscrollbar.min'
], function(_, Backbone){
  
  var _timezone = (new Date).getTimezoneOffset();
  var _testDateIOSTrans = /invalid/.test((new Date( (new Date).toISOString() )).toString().toLowerCase());
  
  function _dialog(msg, type, layout, buttons){
	var n = noty({
		type: type||'alert',
		layout: layout||'bottomLeft',
		theme: 'defaultTheme',
		dismissQueue: true,
		timeout: 6180/2,
		maxVisible: 7,
		closeWith: ['click'], 
		text: msg,
		buttons: buttons
	});
	return n;
  };
  
  var utils = {
  
	//发起一个简单的get请求
	doSyncGet: function(url, callback){
		var model = this;
		var req = {};
		req.url = url;
		req.data = {};
		req.dataType = 'json';
		req.complete = function(xhr, status){
			var ret = utils.parseAjax(xhr.responseJSON);
			if (!ret) return;
			callback(ret, xhr.responseJSON);
		};
		return Backbone.sync('read', new Backbone.Model, req);
	},
  
	//统一处理ajax返回值
	parseAjax: function(data){
		var code = parseInt(data.errcode);
		if(code === 401){
			location.href = 'login.html';
		}else if(isNaN(code) || code !== 0){
			window._appFacade.router.navigate('error/'+data.errcode+'/'+data.errmsg, {
				trigger: true
			});
			return null;
		}else{ //success
			if ('clientRoute' in data.result) {
				if ( /^\#/.test(data.result.clientRoute) ){
					window._appFacade.router.navigate(data.result.clientRoute, {
						trigger: true
					});
				}else{
					window.location.href = data.result.clientRoute;
				}
				return null;
			}
		
			return data.result;
		}
	},
	
	//为dom元素添加自定义滚动条UI
	applyCustomScrollbar: function(outerDom, innerDom, outerHeight, innerHeight){
		outerDom.height(outerHeight);
		innerDom.height(innerHeight).find('.viewport').height(innerHeight);
		innerDom.tinyscrollbar().find('.thumb').css('opacity', .5).hide();
		var scrollbarData = innerDom.data("plugin_tinyscrollbar");
		scrollbarData.update("relative");
	},
	showScrollbar: function(e){
		var $sbar = this.$el.find('.scrollbar');
		if ($sbar.hasClass('disable')) return;
		$sbar.find('.thumb').show();
	},
	hideScrollbar: function(e){
		var $sbar = this.$el.find('.scrollbar');
		if ($sbar.hasClass('disable')) return;
		$sbar.find('.thumb').hide();
	},
	
	parsePlaceholder: function() {
		if ('placeholder' in document.createElement('input')) return;
		
		jQuery('input[placeholder]').each(function(idx, input){
			var jqInput=jQuery(input),
				placeholder=jqInput.attr("placeholder"), 
        cloneIpt = jQuery('<input type="text" class="'+ input.className +' placeholder" value="'+placeholder+'" />'),
				parsed = jqInput.data('phParsed');
			if (parsed) return;
			
      jqInput.before(cloneIpt);
      
			if(input.value=="" || input.value==placeholder){
				cloneIpt.show();
        jqInput.hide();
			}
			else{
				jQuery(input).removeClass("placeholder")
			}
			cloneIpt.bind('focus',function(){
				if(this.value==placeholder){
					cloneIpt.hide();
          jqInput.show();
				}
			});
      jqInput.bind('blur', function(){
				if(this.value==""){
					cloneIpt.show();
          jqInput.hide();
				}
			});
      
			jqInput.data('phParsed', true);
		});
	},
	
	string: {
		trim: function(str){return str.replace(/(^\s+|\s+$)/g, '');}
	},
	
	time: {
		//取得某月的所有日期
		getDatesOfMonth: function(p_year, p_month /*0起步*/ ) {
			var matrix = [
					[]
				],
				putDay = function (d, isLastRow) {
					if (typeof isLastRow == 'undefined') isLastRow = false;
					matrix[matrix.length - 1].push(d == null ? null : d.toISOString());
					if (!isLastRow && matrix[matrix.length - 1].length == 7) {
						matrix[matrix.length] = [];
					}
				};
			var day = new Date; //new Date('2000-00-00');
			day.setFullYear(p_year);
			day.setDate(1); //注意顺序 否则2月可能在遇到30号时出错
			day.setMonth(p_month);
			day.setHours(0);
			day.setMinutes(0);
			day.setSeconds(0);
			day.setMilliseconds(0);
			for (var i = 0, lng = day.getDay(); i < lng; i++) {
				putDay(null);
			}
			while (day.getMonth() == p_month) {
				putDay(day);
				day.setDate(day.getDate() + 1);
			}
			if (!matrix[matrix.length - 1].length) {
				matrix.pop();
			}
			while (matrix[matrix.length - 1].length < 7) {
				putDay(null, true);
			}
			return matrix;
		},
		
		//根据iso字符串取得Date对象
		getFixedIOSDate: function(isoStr, needTrans){ /*IOS等系统无法直接转换isostring*/ 
			try{ 
				if (_testDateIOSTrans || needTrans){ 
					var s = isoStr.replace('T', ' ').replace('Z', '').replace(/\-/g, '/').replace(/\..*$/, ''); 
					var d = new Date(s); 
					d.setHours( d.getHours() - parseInt(_timezone/60) ); 
					return d; 
				} 
			}catch(ex){}

			//解决IE6等不能处理iso字符串 http://momentjs.com/
			if (window.moment){
				return new Date(moment(isoStr).valueOf());
			}
			
			return new Date(isoStr); 
		}
	},
	
	dialog: { //http://ned.im/noty/#creating-a-noty
		alert: function(msg){
			return _dialog(msg);
		},
		success: function(msg){
			return _dialog(msg, 'success');
		},
		error: function(msg){
			return _dialog(msg, 'error');
		},
		warning: function(msg){
			return _dialog(msg, 'warning');
		},
		infomation: function(){
			return _dialog(msg, 'infomation');
		},
		confirm: function(msg, btn1Label, btn2Label, btn1Callback, btn2Callback){
			var b1 = {addClass:'button1', text:btn1Label},
				b2 = {addClass:'button1', text:btn2Label};
			if (btn1Callback) b1.onClick = btn1Callback;
			if (btn2Callback) b2.onClick = btn2Callback;
			return _dialog(msg, 'confirm', 'center', [b1, b2]);
		}
	}
	
  };
  
  return utils;
  
});
