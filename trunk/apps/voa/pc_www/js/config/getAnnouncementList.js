define({ "id": "getAnnouncementList", get: function(promise, ajax, args, $, _) {
	var results = {"errcode":0,"errmsg":"debug info-> page:0, query:",
		"result":{"listName":"\u516c\u544a","query":"","page":"0","hasNextPage":1,
			"list":[
				{"id":7231,"title":"\u5e08\u5085\u8ba9\u5996\u602a\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u61ff","time":"2013-5-6 9:45AM"},
				{"id":4915,"title":"\u4e8c\u5e08\u5144\u8ba9\u5996\u602a\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u5149","time":"2013-5-6 9:45PM"},
				{"id":1739,"title":"\u5c0f\u5e08\u59b9\u4e5f\u8ba9\u5996\u602a\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u7f38","time":"2013-5-6 9:45AM"},
				{"id":9242,"title":"\u5927\u4f19\u5168\u8ba9\u5996\u602a\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u76f8\u5982","time":"2013-5-6 9:45AM"},
				{"id":6674,"title":"\u5e08\u5085\u8ba9\u5996\u602a\u6293\u8d70\u4e86\u5e08\u5085\u8ba9\u5996\u602a\u6293\u8d70\u4e86\u6293\u8d70\u4e86\u6293\u8d70\u4e86\u6293\u8d70\u4e86\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u61ff","time":"2013-5-6 9:45AM"},
				{"id":3518,"title":"\u5e08\u5085\u8ba9\u5996\u602a\u8ba9\u5996\u602a\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u61ff","time":"2013-5-6 9:45AM"},
				{"id":9974,"title":"\u5e08\u5085\u8ba9\u5996\u602a\u6293\u8d70\u4e86\u6293\u8d70\u4e86\u6293\u8d70\u4e86","author":"\u53f8\u9a6c\u61ff","time":"2013-5-6 9:45AM"}
			]
		}};
	var cache = $.cache.get('getAnnouncementList');
	if (!cache) {
		ajax({
			url: '/api/notice/get/list',
			type: 'get',
			dataType: 'json',
			success: function (resp) {
				if (resp.errcode != 0) {
					results.errcode = 401;
					results.msg = resp.errmsg;
				} else {
					results.result.list = {};
					results.result.list = _.map(resp.result.list, function(item, key){   
						item.id = item.nt_id;
						item.title = item.subject;
						item.content = item.message;
						var dt  = new Date(parseInt(item.timestamp)*1000);
						item.time = dt.getUTCFullYear()+'-'+dt.getUTCMonth()+'-'+dt.getUTCDate();

						return item;
					});
				}
				$.cache.set('getAnnouncementList', results);
				args.responseJSON = results;
				args.success( results);
			}
		});
	} else {
		args.responseJSON = cache;
		args.success( cache);

	}

	//args.complete({responseJSON: results});
	//promise.resolve(args);
}});
