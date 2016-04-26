define({ "id": "init", get: function(promise, ajax, args, $) {
	
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
											//适用于所有get请求
											// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		"errmsg": '',
		"result": {
			"user": { 
				"name": "勒夫",
				"face": "http://news.jrcq.cn/uploads/allimg/140618/6-14061Q1251I63.jpg",
			},
			"copyright": "Copyright&copy;2014<br/>畅移(上海)信息科技有限公司",
			"fragments": { 
				"addressbook": { 
					"search": { 
						"placeholder": "输入人名搜索",
						"urlRoot": "/addressbook/search"
					},
				},
			},
		}
	};
	if ($.cookie('pc_app_userdata')) {
		var user = $.parseJSON($.cookie('pc_app_userdata'));
		if (user) {
			results.result.user.name = user.realname; 
			results.result.user.face = user.face; 
		} else {
			results.errcode = 401;
			results.errmsg = '登陆异常，请先登陆。';
		}
	} else {
		results.errcode = 401;
		results.errmsg = '没有登陆，请先登陆。';
	} 
	args.complete({responseJSON:results});
}});
