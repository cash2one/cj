/**
 * Created by zhuxun37 on 15/4/10.
 */

define(['zepto', 'underscore', 'frozen', 'h5mod/jweixin-error'], function($, _, fz, jweixin_error) {

	function QyJsApi(wx) {
		this._wx = wx;
	}

	QyJsApi.prototype = {
		/**
		 * 编辑地址所需的参数
		 * @param {object} data
		 * @returns {boolean}
		 */
		'edit_addr': function(data, cb) {
			// 如果 WeixinJSBridge 未定义
			if (_.isUndefined(WeixinJSBridge)) {
				$.dialog({
					title:'',
					content:'没有调用到微信JS接口',
					button:["确认"]
				});
				return false;
			}

			// 判断 cb 是否函数
			if (!_.isFunction(cb)) {
				cb = null;
			}

			WeixinJSBridge.invoke('editAddress', data, function(res) {
				// 如果含有 userName
				if (!_.has(res, "userName")) {
					return false;
				}

				// 地址信息
				var address = {
					"name": res.userName,
					"phone": res.telNumber,
					"adr": res.addressCitySecondStageName + " " + res.addressCountiesThirdStageName + " " + res.addressDetailInfo
				};
				// 如果是函数
				if (_.isFunction(cb)) {
					cb(address);
				}
			});
		},
		'report_err': function(data, type) {

			if (_.isUndefined(type)) {
				type = 'error';
			}

			var jerr = new jweixin_error();
			jerr.errhandle(data, 'getBrandWCPayRequest', type, true, true);
			return true;
		},
		'pay': function(data, cb) {

			// 判断 cb 是否函数
			if (!_.isFunction(cb)) {
				cb = null;
			}

			var self = this;
			WeixinJSBridge.invoke('getBrandWCPayRequest', data, function(res) {

				var st = 'error';
				if (res.err_msg == 'get_brand_wcpay_request:ok') {
					st = 'ok';
				} else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
					st = 'cancel';
				} else {
					self.report_err(res, st);
				}

				cb(res, st);
			});
		},
		'timeline': function(data) {
			if (!_.has(data, 'title') || _.isString(data.title)) {
				data.title = '';
			}

			if (!_.has(data, 'link') || _.isString(data.link)) {
				data.link = '';
			}

			if (!_.has(data, 'img_url') || _.isString(data.img_url)) {
				data.img_url = '';
			}

			this._wx.onMenuShareTimeline({
				title: data.title, // 分享标题
				link: data.link, // 分享链接
				imgUrl: data.img_url, // 分享图标
				success: function () {
					$.dialog({
						title:'',
						content:'成功分享到朋友圈',
						button:["确认"]
					});
				},
				cancel: function () {
					// 用户取消分享后执行的回调函数
				}
			});

			return true;
		},
		'appmsg': function(data) {
			if (!_.has(data, 'title') || _.isString(data.title)) {
				data.title = '';
			}

			if (!_.has(data, 'desc') || _.isString(data.desc)) {
				data.desc = '';
			}

			if (!_.has(data, 'link') || _.isString(data.link)) {
				data.link = '';
			}

			if (!_.has(data, 'img_url') || _.isString(data.img_url)) {
				data.img_url = '';
			}

			if (!_.has(data, 'type') || _.isString(data.type)) {
				data.type = '';
			}

			if (!_.has(data, 'data_url') || _.isString(data.data_url)) {
				data.data_url = '';
			}

			this._wx.onMenuShareAppMessage({
				title: data.title, // 分享标题
				desc: data.desc, // 分享描述
				link: data.link, // 分享链接
				imgUrl: data.img_url, // 分享图标
				type: data.type, // 分享类型, music、video或link，不填默认为link
				dataUrl: data.data_url, // 如果type是music或video，则要提供数据链接，默认为空
				success: function () {
					$.dialog({
						title:'',
						content:'成功分享给朋友',
						button:["确认"]
					});
				},
				cancel: function () {
					// 用户取消分享后执行的回调函数
				}
			});
		},
		'shareqq': function(data) {
			if (!_.has(data, 'title') || _.isString(data.title)) {
				data.title = '';
			}

			if (!_.has(data, 'desc') || _.isString(data.desc)) {
				data.desc = '';
			}

			if (!_.has(data, 'link') || _.isString(data.link)) {
				data.link = '';
			}

			if (!_.has(data, 'img_url') || _.isString(data.img_url)) {
				data.img_url = '';
			}

			this._wx.onMenuShareQQ({
				title: data.title, // 分享标题
				desc: data.data, // 分享描述
				link: data.link, // 分享链接
				imgUrl: data.img_url, // 分享图标
				success: function () {
					$.dialog({
						title:'',
						content:'成功分享到QQ好友',
						button:["确认"]
					});
				},
				cancel: function () {
					// 用户取消分享后执行的回调函数
				}
			});
		}
	};

	return QyJsApi;
});
