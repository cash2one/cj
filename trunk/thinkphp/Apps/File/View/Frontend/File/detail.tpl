<include file="Header" />
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>文件详情</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
	<link rel="stylesheet" href="{$static_path}/css/ionicons.min.css">
	<link rel="stylesheet" href="{$static_path}/css/index.css">
	<script src="{$static_path}/js/jquery.min.js"></script>
</head>
<body>
<div class="warp1">
	<div class="exhibition-wrap">
		<div class="detail-wrap" id="gb_list">

		</div>
	</div>
</div>
<div class="footer">
	<div class="d-inline-b">
		<a href="#" class="option-btn border-btn download">
			<i class="ion-ios-cloud-download-outline download-sized-inline-b "></i>
			<span class="d-inline-b">下载</span>
		</a>
	</div>
	<div class="d-inline-b">
		<a href="{$commenturl}" class="option-btn">
			<i class="ion-ios-chatbubble-outline comment-size d-inline-b"></i>
			<span >评论({$comment_count})</span>
		</a>
	</div>
</div>
<div class="modal-dialog fade">
	<div class="modal-body">
		<div class="exhibition-download-info">
			<span>正在下载</span>
			<span class="file_size">(0MB)</span>
			<span class="download-info-percent">0%</span>
		</div>
		<div>
			<div class="progress-outer">
				<div class="progress-inner"></div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div class="mlr">
			<a href="{$fileurl}" class="btn btn-block btn-primary cancel">取消下载</a>
		</div>
	</div>
</div>
<div class="modal-backdrop fade"></div>

<script>
	// 读取列表
	$(document).ready(function (e) {

		$.get('{$listurl}', function (data) {
			// 如果出错了
			if (0 < data.errcode) {
				alert(data.errmsg);
				return false;
			}

			// 初始化显示的页面内容
			var content = '';
			// 接口返回结果
			var res = data['result'];
			// 取文件后缀
			var fix = res.file_name.replace(/.+\./, "").toLowerCase();

			// 根据文件后缀，执行不同操作
			if (fix == "gif" || fix == "png" || fix == "jpg") {
				content = '<img src="http://' + window.location.host + res.at_attachment + '" title="' +res.file_name+ '" class="exhibition-img"/>';
			} else if (fix == "txt") {
				content = '<pre>' + res.file_content + '</pre>';
			} else {

				// 文件后缀
				var arr_suffix = ["xls", "xlsx", "doc", "docx", "txt", "pdf", "ppt", "rar", "zip"];

				// 类型图标不存在，显示未知文件图标what
				if ((arr_suffix.indexOf(fix, arr_suffix)) < 0) {
					var icon = 'what';
				} else if(fix == 'doc' || fix == 'docx') {
					var icon = 'word';
				} else if(fix == 'xls' || fix == 'xlsx') {
					var icon = 'excel';
				}else {
					var icon = icon;
				}

				// 文件大小格式化
				res.file_size = formatSize(res.file_size);

				content = '<div class="exhibition-file-wrap"><img src="{$static_path}/images/file/' +icon+ '.png" title="' +icon+ '" /><div class="detail-line mt"><span class="detail-file-name">' + res.file_name + '</span></div><div class="detail-line"><span class="detail-file-timestamp">' + res.file_created + '</span><span class="detail-file-uploader"> ' + res.member_username + ' </span><span class="detail-file-size">' + res.file_size + '</span></div></div>';
			}

			if ('' == res) {
				alert('文件不存在');
			} else {
				$('#gb_list').append(content);
			}

			return true;
		});

		// 下载
		$(".download").on('click', function () {
			location.href = '{$downdurl}';
		});

		// 取消
		$(".confirm").on('click', function () {
			history.go(-1);
		});

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

	// 文件长度
	var filesize = 0;

	// 初始化页面
	function Init() {
		document.getElementsByTagName('html')[0].style.fontSize = document.documentElement.clientWidth / 10 + "px";
	}

	// 弹出框
	function setPopup() {
		$(".modal-dialog").addClass('in');
		$(".modal-backdrop").addClass('in');
	}

	// 设置文件长度
	function setFileSize(fsize, file_size) {
		filesize = fsize;
		$(".file_size").html("(" + file_size + ")");
	}

	// 设置已经下载的,并计算百分比
	function setDownloaded(fsize) {
		if (filesize > 0) {
			var percent = Math.round(fsize * 100 / filesize);
			$(".progress-inner").css("width", percent + "%");
			if (percent > 0) {
				$(".download-info-percent").html(percent + "%");
				if (percent == 100) {
					$(".mlr").html("<a href=\"javascript:void(0)\" class=\"btn btn-block btn-primary confirm\">确定</a>");
				}
			} else {
				$(".download-info-percent").html("0%");
			}
		}
	}
</script>

<php>

    // 初始化页面
    echo "<script>Init();</script>";
    // 判断是否下载
	if($down_load=="YES"){
		// 开启缓存
		ob_start();
		// 设置该页面最久执行时间为100秒
		@set_time_limit($execute_time);
		// 打开下载文件
		$file = fopen($file_url, "rb");
		if ($file) {
			// 在前台显示弹出框
			echo "<script>setPopup();</script>";
			// 获取文件大小
			$filesize = filesize($file_url);
			// 不是所有的文件都会先返回大小的，有些动态页面不先返回总大小，这样就无法计算进度了
			if ($filesize != -1) {
			echo "<script>setFileSize($filesize,'".$file_size."');</script>";//在前台显示文件大小
			}

			// 打开存储文件
			$newf = fopen($file_name, "wb");
			$downlen = 0;
			if ($newf) {
				while (!feof($file)) {
					//默认获取8K
					$data = fread($file, 1024 * 8);
					//累计已经下载的字节数
					$downlen += strlen($data);
					fwrite($newf, $data, 1024 * 8);
					//在前台显示已经下载文件大小
					echo "<script>setDownloaded($downlen);</script>";
				}
			}

			// 关闭下载文件
			if ($file) {
				fclose($file);
			}

			// 关闭存储文件
			if ($newf) {
				fclose($newf);
			}
		}
    }
</php>
</body>
</html>