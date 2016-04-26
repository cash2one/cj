define({ "id": "getCheckInDaily", get: function(promise, ajax, args, $, _) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
		"errmsg": '',
		"result": {
			"pageName": "\u6bcf\u65e5\u7b7e\u5230",
			"notice": "\u65e9\u4e0a\u597d\uff0c\u632f\u594b\u7684\u4e00\u5929\u5f00\u59cb\u4e86\uff01", 
			"day": "12<small>\u6708<\/small>12<small>\u65e5<\/small> \u661f\u671f\u4e8c",
			"list": [
					{
						"type": 0,
						"title": "\u4e0a\u73ed", 
						"fixTime": "9:00", 
						"current": false,
						"realTime": "",
   						"btn": {
							"label": "签到"
						},
						"geo": {
							"ip": "",
							"telecom": ""
						}
					},
					{
						"type": 1,
						"title": "\u4e0b\u73ed",
						"fixTime": "18:00",
						"current": false,
						"realTime": "",
						"btn": {
							"label": "\u7b7e\u9000"
						},
						"geo": null
					}
			]
		}
	};
	
	ajax({
		url: '/api/sign/get/records',
		type: 'get',
		dataType: 'json',
		success: function (resp) {
			if (resp.errcode != 0) {
				results.errcode = resp.errcode;
				results.msg = resp.errmsg;
			} else {
				$.each(resp.result.records, function (key, item){
					if (item.signtime) {	
						results.result.list[item.type - 1].btn = null;
						var dt = new Date(parseInt(item.signtime) * 1000);
						results.result.list[item.type - 1].realTime = dt.getHours()+":"+dt.getMinutes();
						results.result.list[item.type - 1].geo = {};
						results.result.list[item.type - 1].geo.ip = item.ip;
					}
				});
			}
			args.responseJSON = results;
			promise.resolve(args);
		}
	});
	args.complete({responseJSON:results});
}, post: function (promise, ajax, args, $, _) {
	var results = {
		errcode: 0,
		errmsg: '',
		result: {
			type: 1,
			title: '下班',
			fixTime: '18: 00',
			current: true,
			realTime: '',
			geo: {
				ip: '', 
				telecom: ''
			},
			btn: null,
		}
	};
	ajax({
		url: '/api/sign/post/sign',
		type: 'get',
		dataType: 'json',
		success: function (resp) {
			if (resp.errcode != 0) {
				results.errcode = resp.errcode;
				results.msg = resp.errmsg;
			} else {
				if (resp.result) {	
					results.result.type = resp.result.type - 1;
					var dt = new Date(parseInt(resp.result.signtime) * 1000);
					results.result.realTime = dt.getHours()+":"+dt.getMinutes();
					results.result.current = true;
					results.result.geo.ip = resp.result.ip;
				}
			}
			args.responseJSON = results;
			promise.resolve(args);
		}
	});

}
});
