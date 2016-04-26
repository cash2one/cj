define({ 
	"id": "settingsPassword", 
	get: function(promise, ajax, args) {
		var results = {
			"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
			"errmsg": '',
			"result": {
				"pageName": "修改密码", 
				"label": {
					"old": "\u8f93\u5165\u65e7\u5bc6\u7801",
					"new": "\u8f93\u5165\u65b0\u5bc6\u7801", 
					"re": "\u91cd\u590d\u65b0\u5bc6\u7801"
				},
				"pattern": "^[a-zA-Z0-9_-]{6,}$",
				"validNotice": {
					"invalid": "\u8bf7\u8f93\u5165\u6b63\u786e\u7684\u683c\u5f0f\uff08\u4e0d\u5c11\u4e8e6\u4f4d\u7684\u5b57\u6bcd\u6216\u6570\u5b57\uff09\uff01", 
					"different": "\u4e24\u6b21\u65b0\u5bc6\u7801\u8f93\u5165\u4e0d\u4e00\u81f4\uff0c\u8bf7\u91cd\u8bd5"
				},
				"buttons": {
					"cancel": {"label": "\u53d6 \u6d88"},
					"submit": {
						"label": "\u786e \u8ba4", 
						"confirm": {
							"message": "\u786e\u5b9e\u8981\u4fee\u6539\u5bc6\u7801\u5417?", 
							"no": "\u5426",
							"yes": "\u662f"
						}
					}
				}
			}
		};
		args.responseJSON = results;

		promise.resolve(args);
	},
	post: function (promise, ajax, args, $, _) {
		var results = {
			"errorcode": 0, 
			"msg": '',
			"result":{"success": true, "message": '您的密码已经修改！ <a href="#settings/password" style="text-decoration:underline">再次修改</a>'},
			//"result":{"success": false, "message": "\u65e7\u5bc6\u7801\u8f93\u5165\u9519\u8bef, \u8bf7\u91cd\u65b0\u8f93\u5165"}
		};
		ajax({
			url: '/api/addressbook/put/password',
			type: 'get',
			data: {"newpw": $.md5(args.data.new), "pw": $.md5(args.data.old)},
			dataType: 'json',
			success: function (resp) {
				if (resp.errcode != 0) {
					results.errcode = resp.errcode;
					results.msg = resp.errmsg;
					results.result.success = false;
					results.result.message = resp.errmsg;
				} 
				args.responseJSON = results;
				promise.resolve(args);
			}
		});
	}
});
