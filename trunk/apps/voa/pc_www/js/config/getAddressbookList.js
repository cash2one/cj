define({ "id": "getAddressbookList", get: function(promise, ajax, args, $, _) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
											//适用于所有get请求
											// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		"errmsg": '',
		"result": {
			'listName': '通讯录',
			'query': '', //注意处理一下用户输入
			'page': 0,
			'hasNextPage': 0, //是否还有下一页
			'groups': [
				'最近联系人',
				'研发中心',
				'测试中心',
				'用户体验部'
			], 
			"list":[
				{"id":1234,"name":"\u53f8\u9a6c\u61ff","job":"\u7537\u79d8\u82cf","group":0},
				{"id":2234,"name":"\u6b27\u9633","job":"\u7ba1\u7406\u5458","group":0},
				{"id":3234,"name":"De Rossi","job":"\u4e2d\u573a","group":1},
				{"id":4234,"name":"\u5e73\u6258","job":"\u6559\u7ec3","group":2},
				{"id":5234,"name":"\u5df4\u6d1b\u7279\u5229","job":"\u524d\u950b","group":2},
				{"id":6234,"name":"\u5409\u7530\u4e9a\u7eaa\u5b50","job":"\u6b4c\u624b","group":2},
				{"id":7234,"name":"\u5218\u6613\u65af","job":"\u4e8c\u4f20","group":3}
			]
		}
	};
	var search = {};
	if (args.url) {
		urlParams = args.url.split('/');
		console.log(urlParams);
		search.realname = urlParams[4];
	}

	$.when(ajax({
		url: '/api/addressbook/get/departments',
		type: 'get',
		dataType: 'json',
		success: function (resp) {
			if (resp.errcode != 0) {
				results.errcode = 401;
				results.msg = resp.errmsg;
			} else {
				results.result.groups = resp.result.lists;
			}
		}
	})).done(ajax({
			url: '/api/addressbook/get/list',
			type: 'get',
			data: search, 
			dataType: 'json',
			success: function (resp) {
				if (resp.errcode != 0) {
					results.errcode = 401;
					results.msg = resp.errmsg;
				} else {
					results.result.list = {};
					results.result.list = _.map(resp.result.list, function(item, key){   
						item.name = item.realname;
						item.job = item.jobtitle;
						item.id = item.uid;
						return item;
					});
					
				}
				args.responseJSON = results;
				args.success( results);
			}
		})
	);
	//promise.resolve(args);
}});
