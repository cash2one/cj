define({ "id": "getCheckInMenu", get: function(promise, ajax, args) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
											//适用于所有get请求
											// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		"errmsg": '',
		"result": {
			"listName": "\u7b7e\u5230",
			"list": [
				{
					"title": "\u6bcf\u65e5\u7b7e\u5230",
					"url": "#checkin\/daily"
				},
				{
					"title": "\u8003\u52e4\u67e5\u8be2", 
					"url":"#checkin\/calendar"
				}
			]	
		}
	};
	args.responseJSON = results;

	args.complete({responseJSON: results});
	//promise.resolve(args);
}});
