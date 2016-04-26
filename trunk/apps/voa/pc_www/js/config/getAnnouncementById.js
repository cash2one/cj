define({ "id": "getAnnouncementById", get: function(promise, ajax, args, $, _) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
											//适用于所有get请求
											// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		"errmsg": '',
		"result": {
			"pageName": "\u516c\u544a\u8be6\u60c5","id":"7231",
			"title": "\u7aef\u5348\u8282315\u653e\u5047\u5b89\u6392",
			"author": "\u53f8\u9a6c\u61ff",
			"time": "2013-5-6 9:45AM",
			"content": "xxx"	
		}
	};
	var id = 0;
	if (args.url) {
		urlParams = args.url.split('/');
		id = urlParams[2];
	}
	if (!id) {
		return ;
	}
	var cache = $.cache.get('getAnnouncementList');
	if (cache) {
		console.log('getcache');
		console.log(cache);
		var result = _.find(cache.result.list, function (item, k) {
			return item.id == id;
		});
		result.pageName = result.title;
		results.result = result;

		console.log('results=====');
		console.log(results);
		args.responseJSON = results;
		promise.resolve(args);
		/*
		ajax({
			url: '/api/notice/get/detail',
			type: 'get',
			data: {nt_id: id},
			dataType: 'json',
			success: function (resp) {
				if (resp.errcode != 0) {
					results.errcode = resp.errcode;
					results.msg = resp.errmsg;
				} else {
					results.result.pageName = resp.result.message;
					results.result.content = resp.result.message;
					results.result.id = resp.result.cabid;
					results.result.name = resp.result.realname;
					results.result.infos.department.value = resp.result.department;
					results.result.infos.job.value = resp.result.jobtitle;
					results.result.infos.mobile.value = resp.result.mobilephone;
					results.result.infos.phone.value = resp.result.telephone;
					results.result.infos.email.value = resp.result.email;
				}
				args.responseJSON = results;
				promise.resolve(args);
			}
		});
		*/
	}
}});
