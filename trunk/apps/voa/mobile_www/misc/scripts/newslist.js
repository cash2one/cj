/**
 * Created by ChangYi on 2015/8/18.
 * 新闻公告列表
 */

define(['zepto','frozen'], function($){
	/**
	 * 构造
	 */
	function NewsList(){

	}

	NewsList.prototype = {
		init: function(url, name, id) {
			// 监听 回车 按键
			var input_key = $('input[name = "keyword"]');
			var self = this;
			input_key.on("keyup", function(e) {
				self._searchword(e);
				return true;
			});
			var keyword = this._urlparam(url, name);
			this._input_serch(keyword, id, input_key);
			return keyword;
		},
		_urlparam:function(url, name){
			var pattern = new RegExp("[?&]"+name+"\=([^&]+)", "g");
			var matcher = pattern.exec(url);
			var items = '';
			if(null != matcher){
				try{
					items = decodeURIComponent(decodeURIComponent(matcher[1]));
				}catch(e){
					try{
						items = decodeURIComponent(matcher[1]);
					}catch(e){
						items = matcher[1];
					}
				}
			}
			return items;
		},
		_searchword:function(e){
			if (e.which === 13) { //回车
				var cur_url = window.location.href;
				var reg = new RegExp("\\??keyword=(.*?)$", 'ig');
				cur_url = cur_url.replace(reg, '');
				cur_url += (-1 == cur_url.indexOf('?') ? '?' : '&') + 'keyword=' + $('input[name = "keyword"]').val();
				window.location.href = cur_url;
			}
			return true;
		},
		_input_serch:function(w, id, input_key){
			if(w != ''){
				input_key.val(w);
				$('.ui-searchbar-wrap').addClass('focus');
				$('.ui-searchbar-input input').focus();
			}
			$('.ui-searchbar').tap(function(){
				$('.ui-searchbar-wrap').addClass('focus');
				$('.ui-searchbar-input input').focus();
			});
			$('.ui-searchbar-cancel').tap(function(){
				input_key.val('');
				$('.ui-searchbar-wrap').removeClass('focus');
				if(w){
					window.location.href = '/frontend/news/category/?nca_id='+id;
				}
			});
			$('.ui-icon-close').tap(function(){
				input_key.val('');
			});
			return true;
		}
	}

	return NewsList;

})