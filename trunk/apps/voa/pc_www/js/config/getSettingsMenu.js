define({ "id": "getSettingsMenu", get: function(promise, ajax, args) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
											//适用于所有get请求
											// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		"errmsg": '',
		"result": {
			"listName": "\u8bbe\u7f6e",
			"list": [
				{
					"title": "\u4e2a\u4eba\u4fe1\u606f",
					"url": "#settings\/profile"
				},
				{
					"title": "\u4fee\u6539\u5bc6\u7801", 
					"url":"#settings\/password"
				}
			]	
		}
	};
	args.responseJSON = results;

	args.complete({responseJSON: results});
	//promise.resolve(args);
}});
