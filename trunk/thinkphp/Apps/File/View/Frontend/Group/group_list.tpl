<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>文件分组列表{$location}</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <link rel="stylesheet" href="{$static_path}/css/ionicons.min.css">
    <link rel="stylesheet" href="{$static_path}/css/index.css">
    <script src="{$static_path}/js/main.js"></script>
    <script src="{$static_path}/js/jquery.min.js"></script>
</head>
<body>
    <div class="warp2">
        <div class="list-wrap">
			<ul class="list-ul" id="gb_list">

				<foreach name="list" item="v">

				</foreach>
			</ul>
			<ul>
				<a id="show_more" href="javascript:void(0);" onclick="js_init()" style="color:#797C80" class="mod_ajax_more">加载更多&gt;&gt;</a>
			</ul>
        </div>
    </div>
	<div id="nodata" class="nodata">
	</div>
</body>
</html>

<script>

	if('YES'=='{$location}'){ location.href="{$f_url}"}
	// 读取列表
	$.get('{$listurl}?limit={$limit}&page={$page}', function(data) {

		// 如果出错了
		if (0 < data.errcode) {
			alert(data.errmsg);
			return false;
		}

		var strs = '';
		var list = data['result']['data'];
		// 循环列表
		for (var k in list) {
			// 获取随机数
			var pic = parseInt(7*Math.random()+1);
			strs += '<li><section class="list-item"><a href="{$folderurl}?f_id='+list[k].group_id+'"><div class="list-left"><img src="{$static_path}/images/group/group'+pic+'.jpg" alt="" class="defaultList-avatar"/></div><div class="list-right"><span class="group-name">'+list[k].group_name+'</span></div></a></section></li>';
		}

		if ('' == strs) {
			document.getElementById('show_more').style.display = "none";
			alert('当前没有任何分组');
		} else {
			$('#gb_list').append(strs);
		}

		return true;
	});

	// 下一页页码
	var nextpage = {$page};

	// 点击加载下一页
	function js_init() {
		nextpage++;
		$.get('{$listurl}?limit={$limit}&page='+nextpage, function(data) {
			// 如果出错了
			if (0 < data.errcode) {
				alert(data.errmsg);
				return false;
			}

			var strs = '';
			var list = data['result']['data'];
			// 循环列表
			for (var k in list) {
				// 获取随机数
				var pic = parseInt(7*Math.random()+1);
				strs += '<li><section class="list-item"><a href="{$folderurl}?f_id='+list[k].group_id+'"><div class="list-left"><img src="{$static_path}/images/group/group'+pic+'.jpg" alt="" class="defaultList-avatar"/></div><div class="list-right"><span class="group-name">'+list[k].group_name+'</span></div></a></section></li>';
			}
			// 无数据
			if ('' == strs) {
				document.getElementById('show_more').style.display = "none";
				alert('对不起，已经到底啦！');
			} else {
				$('#gb_list').append(strs);
			}

			return true;
		});
	}

</script>