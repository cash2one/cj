<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>文件管理</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
	<link rel="stylesheet" href="{$static_path}/css/ionicons.min.css">
	<link rel="stylesheet" href="{$static_path}/css/index.css">
	<script src="{$static_path}/js/main.js"></script>
	<script src="{$static_path}/js/jquery.min.js"></script>
</head>
<body>
<form name="search_file" id="search_file" action="{$searchurl}" method="post">
	<div class="option-wrap" id="gb_sbt">
		<div class="search-wrap">
			<i class="ion-search ft-search-icon"></i>
			<input type="text" id="search" name="condition" placeholder="搜索" />
			<input type="hidden" id="qq" name="qq" value="{$group_id}" />
			<input type="submit" id="sub" name="sub" value="提交" style="display: none;"/>
		</div>
	</div>
</form>

<div class="warp3">
	<div class="navbread"><span>{$forder_parents}</span></div>
	<div class="list-wrap navbared-list-wrap">
		<ul>
			<li class="file-share" id="folders">

			</li>
		</ul>

		<ul class="list-ul" id="file">

		</ul>
		<ul>
			<a id="show_more" href="javascript:void(0);" onclick="js_init()" style="color:#797C80" class="mod_ajax_more">加载更多&gt;&gt;</a>
		</ul>
	</div>
</div>
</body>
</html>

<script>

		// 读取列表
		$.get('{$listurl}?f_id={$f_id}&limit={$limit}&page={$page}', function(data) {

			// 如果出错了
			if (0 < data.errcode) {
				alert(data.errmsg);
				return false;
			}

			var files = '';
			var folders = '';
			var list = data['result']['data'];
			// 获取列表
			for (var k in list) {
				// 文件大小格式化
				list[k].file_size = formatSize(list[k].file_size);
				// 当前文件图标格式化并转为小写
				var fix = list[k].file_name.replace(/.+\./, "").toLowerCase();

				var suffix = ["xls", "xlsx", "doc", "docx", "txt", "pdf", "ppt", "rar", "zip"];
				// 判断icon是否存在,不存在则调用默认?标识图片
				if ((suffix.indexOf(fix, suffix)) < 0) {
					var icon = 'what';
				} else if(fix == 'doc' || fix == 'docx') {
					var icon = 'word';
				} else if(fix == 'xls' || fix == 'xlsx') {
					var icon = 'excel';
				} else {
					var icon = list[k].file_name.replace(/.+\./, "");
				}

				// 分离文件夹和文件_文件
				if ({$ftype[1]} == list[k].file_level) {
					// 如果是图片或视频，显示缩略图
					if(fix == "gif" || fix == "png" || fix == "jpg" || fix == "mp4" || fix == "rmvb" || fix == "wmv" || fix == "avi"){
						files += '<li><section class="list-item"><a href="{$fileurl}?file_id='+list[k].file_id+'&group_id={$group_id}"><div class="list-left"><img src="http://' + window.location.host + list[k].at_attachment + '" alt="" class="defaultList-avatar radius-none"/></div><div class="list-right"><div><span class="file-name">'+list[k].file_name+'</span></div><div><span class="file-timestamp">'+list[k].file_created+'</span><span class="file-uploader">'+list[k].member_username+'</span><span class="file-size">'+list[k].file_size+'</span></div></div><i class="ion-ios-arrow-right toArrow"></i></a></section></li>';
					}else {
						files += '<li><section class="list-item"><a href="{$fileurl}?file_id=' + list[k].file_id + '&group_id={$group_id}"><div class="list-left"><img src="{$static_path}/images/file/' + icon + '.png" alt="" class="defaultList-avatar radius-none"/></div><div class="list-right"><div><span class="file-name">' + list[k].file_name + '</span></div><div><span class="file-timestamp">' + list[k].file_created + '</span><span class="file-uploader">' + list[k].member_username + '</span><span class="file-size">' + list[k].file_size + '</span></div></div><i class="ion-ios-arrow-right toArrow"></i></a></section></li>';
					}
					// 分离文件夹和文件_文件夹
				} if ({$ftype[0]} == list[k].file_level) {
					var icon = 'folder';
					folders += '<section class="list-item"><a href="{$folderurl}?f_id='+list[k].file_id+'"><div class="list-left folderImg-wrap"><img src="{$static_path}/images/file/'+icon+'.png" alt="" class="whImg"/></div><div class="list-right"><span>'+list[k].file_name+'</span></div><i class="ion-ios-arrow-right toArrow"></i></a></section>';
				}
			}

			// 判断文件夹是否为空
			if ('' == files && '' == folders) {
				document.getElementById('show_more').style.display = "none";
				alert('当前文件夹下没有文件');
			} else {
				$('#file').append(files);
				$('#folders').append(folders);
			}
			return true;

		});

		// 搜索
		// 监听提交按钮 click 事件
		$("#sub").on('click', function(e) {

			var frm = $("#search_file");

			// ajax 提交
			$.post(frm.attr('action'), frm.serialize(), function(data) {

				// 如果报错
				if (0 !== data.errcode) {
					alert(data.errmsg);
				}
				var files = '';
				var folders = '';
				var list = data['result'];
				// 获取列表
				for (var k in list) {
					// 文件大小格式化
					list[k].file_size = formatSize(list[k].file_size);
					// 当前文件图标格式化并转为小写
					var fix = list[k].file_name.replace(/.+\./, "").toLowerCase();

					var suffix = ["xls", "xlsx", "doc", "docx", "txt", "pdf", "ppt", "rar", "zip"];
					// 判断icon是否存在,不存在则调用默认?标识图片
					if ((suffix.indexOf(fix, suffix)) < 0) {
						var icon = 'what';
					} else if(fix == 'doc' || fix == 'docx') {
						var icon = 'word';
					} else if(fix == 'xls' || fix == 'xlsx') {
						var icon = 'excel';
					} else {
						var icon = list[k].file_name.replace(/.+\./, "");
					}

					// 分离文件夹和文件_文件
					if ({$ftype[1]} == list[k].file_level) {
						// 如果是图片或视频，显示缩略图
						if(fix == "gif" || fix == "png" || fix == "jpg" || fix == "mp4" || fix == "rmvb" || fix == "wmv" || fix == "avi"){
							files += '<li><section class="list-item"><a href="{$fileurl}?file_id='+list[k].file_id+'&group_id={$group_id}"><div class="list-left"><img src="http://' + window.location.host + list[k].at_attachment + '" alt="" class="defaultList-avatar radius-none"/></div><div class="list-right"><div><span class="file-name">'+list[k].file_name+'</span></div><div><span class="file-timestamp">'+list[k].file_created+'</span><span class="file-uploader">'+list[k].member_username+'</span><span class="file-size">'+list[k].file_size+'</span></div></div><i class="ion-ios-arrow-right toArrow"></i></a></section></li>';
						}else {
							files += '<li><section class="list-item"><a href="{$fileurl}?file_id=' + list[k].file_id + '&group_id={$group_id}"><div class="list-left"><img src="{$static_path}/images/file/' + icon + '.png" alt="" class="defaultList-avatar radius-none"/></div><div class="list-right"><div><span class="file-name">' + list[k].file_name + '</span></div><div><span class="file-timestamp">' + list[k].file_created + '</span><span class="file-uploader">' + list[k].member_username + '</span><span class="file-size">' + list[k].file_size + '</span></div></div><i class="ion-ios-arrow-right toArrow"></i></a></section></li>';
						}
						// 分离文件夹和文件_文件夹
					} if ({$ftype[0]} == list[k].file_level) {
						var icon = 'folder';
						folders += '<section class="list-item"><a href="{$folderurl}?f_id='+list[k].file_id+'"><div class="list-left folderImg-wrap"><img src="{$static_path}/images/file/'+icon+'.png" alt="" class="whImg"/></div><div class="list-right"><span>'+list[k].file_name+'</span></div><i class="ion-ios-arrow-right toArrow"></i></a></section>';
					}
				}

				// 判断文件夹是否为空
				if ('' == files && '' == folders) {
					document.getElementById('show_more').style.display = "none";
					alert('无搜索结果');
				} else {
					// 清空列表
					$('#file').empty();
					$('#folders').empty();

					$('#file').append(files);
					$('#folders').append(folders);
				}
				return true;

			});
			return false;

		});

		// 文件大小格式化函数
		function formatSize($size) {
			var size = parseFloat($size);
			var rank = 0;
			var rankchar = 'Bytes';
			while (size > 1024) {
				size = size / 1024;
				rank++;
			}
			if (rank == 1) {
				rankchar = "KB";
			}
			else if (rank == 2) {
				rankchar = "MB";
			}
			else if (rank == 3) {
				rankchar = "GB";
			}
			return size.toFixed(2) + " " + rankchar;
		}

		// 下一页页码
		var nextpage = {$page};

		// 点击加载下一页
		function js_init() {
			nextpage++;
			$.get('{$listurl}?f_id={$f_id}&limit={$limit}&page='+nextpage, function(data) {

				// 如果出错了
				if (0 < data.errcode) {
					alert(data.errmsg);
					return false;
				}

				var files = '';
				var folders = '';
				var list = data['result']['data'];
				// 获取列表
				for (var k in list) {
					// 文件大小格式化
					list[k].file_size = formatSize(list[k].file_size);
					// 当前文件图标格式化并转为小写
					var fix = list[k].file_name.replace(/.+\./, "").toLowerCase();

					var suffix = ["xls", "xlsx", "doc", "docx", "txt", "pdf", "ppt", "rar", "zip"];
					// 判断icon是否存在,不存在则调用默认?标识图片
					if ((suffix.indexOf(fix, suffix)) < 0) {
						var icon = 'what';
					} else if(fix == 'doc' || fix == 'docx') {
						var icon = 'word';
					} else if(fix == 'xls' || fix == 'xlsx') {
						var icon = 'excel';
					} else {
						var icon = list[k].file_name.replace(/.+\./, "");
					}

					// 分离文件夹和文件_文件
					if ({$ftype[1]} == list[k].file_level) {
						// 如果是图片或视频，显示缩略图
						if(fix == "gif" || fix == "png" || fix == "jpg" || fix == "mp4" || fix == "rmvb" || fix == "wmv" || fix == "avi"){
							files += '<li><section class="list-item"><a href="{$fileurl}?file_id='+list[k].file_id+'&group_id={$group_id}"><div class="list-left"><img src="http://' + window.location.host + list[k].at_attachment + '" alt="" class="defaultList-avatar radius-none"/></div><div class="list-right"><div><span class="file-name">'+list[k].file_name+'</span></div><div><span class="file-timestamp">'+list[k].file_created+'</span><span class="file-uploader">'+list[k].member_username+'</span><span class="file-size">'+list[k].file_size+'</span></div></div><i class="ion-ios-arrow-right toArrow"></i></a></section></li>';
						}else {
							files += '<li><section class="list-item"><a href="{$fileurl}?file_id=' + list[k].file_id + '&group_id={$group_id}"><div class="list-left"><img src="{$static_path}/images/file/' + icon + '.png" alt="" class="defaultList-avatar radius-none"/></div><div class="list-right"><div><span class="file-name">' + list[k].file_name + '</span></div><div><span class="file-timestamp">' + list[k].file_created + '</span><span class="file-uploader">' + list[k].member_username + '</span><span class="file-size">' + list[k].file_size + '</span></div></div><i class="ion-ios-arrow-right toArrow"></i></a></section></li>';
						}
						// 分离文件夹和文件_文件夹
					} if ({$ftype[0]} == list[k].file_level) {
						var icon = 'folder';
						folders += '<section class="list-item"><a href="{$folderurl}?f_id='+list[k].file_id+'"><div class="list-left folderImg-wrap"><img src="{$static_path}/images/file/'+icon+'.png" alt="" class="whImg"/></div><div class="list-right"><span>'+list[k].file_name+'</span></div><i class="ion-ios-arrow-right toArrow"></i></a></section>';
					}
				}

				// 判断文件夹是否为空
				if ('' == files && '' == folders) {
					document.getElementById('show_more').style.display = "none";
					alert('对不起，已经到底啦！');
				} else {
					$('#file').append(files);
					$('#folders').append(folders);
				}
				return true;

			});
		}
</script>