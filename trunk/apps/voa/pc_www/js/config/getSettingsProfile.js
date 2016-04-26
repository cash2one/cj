define({ "id": "getSettingsProfile", get: function(promise, ajax, args, $, _) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
		"errmsg": '',
		"result": {
			"pageName": "\u4e2a\u4eba\u4fe1\u606f", 
			"id": 37126, 
			"face": "http:\/\/tp4.sinaimg.cn\/1288915263\/50\/5697533118\/1",
			"name": "\u53f8\u9a6c\u61ff",
			"infos": {
				"department": {
					"label": "\u6240\u5c5e\u90e8\u95e8", 
					"value": "\u56fd\u5bb6\u7d27\u6025\u4e8b\u52a1\u90e8266", 
					"icon": ["img\/icons.png", "0 -46px"]
				},
				"job": {
					"label": "\u804c\u4f4d",
					"value": "\u7537\u79d8\u82cf", 
					"icon": ["img\/icons.png","-46px -49px"]
				},
				"mobile": {
					"label": "\u624b\u673a", 
					"value": "13323353231", 
					"icon":["img\/icons.png","-97px -46px"]
				},
				"phone": {
					"label": "\u5ea7\u673a",
					"value": "07-07-007",
					"icon": ["img\/icons.png","-147px -47px"]
				},
				"email": {
					"label": "\u90ae\u7bb1",
					"value": "sdfs@ddd.com",
					"icon":["img\/icons.png","-197px -46px"]
				}
			}	
		}
	};
	if ($.cookie('pc_app_userdata')) {
		var user = $.parseJSON($.cookie('pc_app_userdata'));
		if (user) {
			results.result.id= user.uid; 
			results.result.name = user.realname; 
			results.result.face = user.face; 
			results.result.infos.department.value = user.department; 
			results.result.infos.job.value = user.jobtitle; 
			results.result.infos.mobile.value = user.mobilephone; 
			results.result.infos.phone.value = user.telephone; 
			results.result.infos.email.value = user.email; 
		} else {
			results.errcode = 401;
			results.errmsg = '登陆异常，请先登陆。';
		}
	} else {
		results.errcode = 401;
		results.errmsg = '没有登陆，请先登陆。';
	}
	args.responseJSON = results;

	promise.resolve(args);
}});
