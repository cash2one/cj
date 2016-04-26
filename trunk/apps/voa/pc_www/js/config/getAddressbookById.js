define({ "id": "getAddressbookById", get: function(promise, ajax, args, $, _) {
	var results = {
		"errcode": 0, //如果返回401，表示权限不足，自动跳转回登陆页
		"errmsg": '',
		"result": {
			'face': "http://tp4.sinaimg.cn/1288915263/50/5697533118/1",
			'id': '22222',
			'name': '司马懿523', //是否还有下一页
			"infos": {
				'department': {
						"label": "所属部门",
						"value": '国家紧急事务部',
						"icon": [
							'img/icons.png',
							'0 -46px'
						]
					},
				'job': {
						"label": "职位",
						"value": '男秘苏',
						"icon": [
							'img/icons.png',
							'-46px -49px'
						]
					},
				'mobile': {
						"label": "手机",
						"value": '13323353231',
						"icon": [
							'img/icons.png',
							'-97px -46px'
						]
					},
				'phone': {
						"label": "座机",
						"value": '07-07-007',
						"icon": [
							'img/icons.png',
							'-147px -47px'
						]
					},
				'email': {
						"label": "邮箱",
						"value": 'sdfs@ddd.com',
						"icon": [
							'img/icons.png',
							'-197px -46px'
						]
					},
			}
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
	
	ajax({
		url: '/api/addressbook/get/profile',
		type: 'get',
		data: {uid: id},
		dataType: 'json',
		success: function (resp) {
			if (resp.errcode != 0) {
				results.errcode = resp.errcode;
				results.msg = resp.errmsg;
			} else {
				results.result.face = resp.result.face;
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
}});
