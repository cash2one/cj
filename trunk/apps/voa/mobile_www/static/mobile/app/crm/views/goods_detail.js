define(["utils/common", "utils/call", "views/base", "data/goods", "utils/render", "text!templates/goods_detail.html", 'jquery', 'underscore'
         , "swipebox", "jquery-carousel", "jquery-lazyload", "css!styles/goods_detail.css"], function(common, call, base, goods, render, tpl, $, _){

    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;

    view.prototype = $.extend(view.prototype, {
    	show_weixin_menu: true,
		slide_photos: [],
		goods_id: null,
		// 分享出去的标识值
		sig: null,
		timestamp: null,
        // 模板处理
        render: function(args) {
			if (!args.id) {
				var params = args[0].split('_');
				this.goods_id = params[0];
				this.sig = params[1];
				this.timestamp = params[2];
			} else {
				this.goods_id = args.id;
				this.sig = args.sig;
				this.timestamp = args.timestamp;
			}


			this.slide_photos = [];
			var self = this;
			var buy_url = common.makeurl('goods_selected', self.goods_id, 'pay');

			goods.get_detail({dataid: this.goods_id, sig: this.sig, timestamp: this.timestamp}, function (ret) {
				document.title = ret.subject;

				var r = new render();
				r.template = tpl;
				r.vars = {buy_url: buy_url, goods: ret};
				if (ret.subject && ret.sales != undefined) {
					$(document).prop('title', ret.subject);
				}
				if (!_.isEmpty(ret.slide)) {
					$.each(ret.slide, function(k, item) {
						self.slide_photos.push({title: '', image: item.url});
					});

				}
				$(window).lazyLoadXT();
				var el = r.apply();
				self.page = el;
				self.event(el);
			});
        },
		// 分享事件监听
		share: function(detail) {

			wx.onMenuShareTimeline({
				title: detail.subject, // 分享标题
				link: "https://" + window.location.hostname + '/frontend/travel/index?', // 分享链接
				imgUrl: '', // 分享图标
				success: function () {
					// 用户确认分享后执行的回调函数
				},
				cancel: function () {
					// 用户取消分享后执行的回调函数
				}
			});

			wx.onMenuShareAppMessage({
				title: '', // 分享标题
				desc: '', // 分享描述
				link: '', // 分享链接
				imgUrl: '', // 分享图标
				type: '', // 分享类型,music、video或link，不填默认为link
				dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
				success: function () {
					// 用户确认分享后执行的回调函数
				},
				cancel: function () {
					// 用户取消分享后执行的回调函数
				}
			});

			wx.onMenuShareQQ({
				title: '', // 分享标题
				desc: '', // 分享描述
				link: '', // 分享链接
				imgUrl: '', // 分享图标
				success: function () {
					// 用户确认分享后执行的回调函数
				},
				cancel: function () {
					// 用户取消分享后执行的回调函数
				}
			});
		},
        // 监听事件
        // el 是本页面的对像
        event: function (el) {
			var self = this;
			/*
			for(k2 in window.userinfo) {
				$('#debug').append(k2 + ':' +  window.userinfo[k2] + '<br/>');
			}*/
        	/*
        	// 调用其它应用
 	        var c = new call;
 	        c.app('contacts', 'contacts', {container: "#contacts_form", input_name_contacts: 'aaa', input_name_deps: 'bbbb', input_type: 'radio'});
 	      */


        	// 文件显示之后监听事件全部写在这里
        	$( document ).on( "pageshow", el, function() {
				if (self.slide_photos.length) {
					var carousel = new $.widgets.Carousel({
						uuid: $("#carousel", el),
						template: $("#carousel_template", el),
						args: {
							"scrollInterval": 600
						},
						value: self.slide_photos
					});
				}
				$('.js-supply-add', el).click(function () {
        			var el_add_btn = $(this);
                	$('#confirm').popup('open');

                	var id = $(this).data('dataid');
                	el.find('#yes').unbind("click");
                	el.find('#yes').click(function() {
                		$.mobile.loading( "show" );
                    	goods.add_from_supply(id, function (ret) {
                    		$.mobile.loading( "hide" );
                    		if (ret.errcode == '0') {
                    			el_add_btn.remove();
                    		} else {
                    			alert(ret.errmsg);
                    		}
                    	});

                    });
        		});
        		// 文件分享
        		$('.js-share-out', el).click(function () {
        			$('#js-share-out', el).popup('open');

        		})
        		$('#js-share-out', el).on('tap', function () {
        				$(this).popup('close');
        		});

        		self.swipebox(el);
        	});

        }
    });

    return view;
});
