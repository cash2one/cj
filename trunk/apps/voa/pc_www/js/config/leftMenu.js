define({ "id": "leftMenu", get: function(promise, ajax, args) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
											//适用于所有get请求
											// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		"errmsg": '',
		"result": {
			"menu": {
				"企业通讯": {
					"actions": [], //预留
					"list": {					
						"通讯录": {
							"icon": [
								"img/icons.png",
								'0 0'
							],
							"url": "#addressbook" //此处和route.js里定义的一致
						},
						"公告": {
							"icon": [
								'img/icons.png',
								'-50px 0'
							],
							"unread": "0",
							"url": "#announcement"
						}
					}
				},
				"常用功能": {
					"actions": [],//预留for"添加常用功能"等
					"list": {					
						"签到": {
							"icon": [
								'img/icons.png',
								'-100px 0'
							],
							"url": "#checkin"
						},
					}
				},
				"其他": {
					"actions": [], //预留
					"list": {					
						"设置": {
							"icon": [
								'img/icons.png',
								'-200px 0'
							],
							"url": "#settings"
						},
					}
				},
			}		
		}
	};
	args.success(results);
}});
