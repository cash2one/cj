{include file="$tpl_dir_base/header.tpl"}


<div class="jumbotron" style="background: #FAFAFA;">
	<h2 id="imqywx_titile" style="color:#5CCA9F;"></h2>

	<p>同步通讯录功能是将微信企业号通讯录成员信息同步至畅移云工作，同步操作可能会需要一些时间（与通讯录成员数有关）</p>
	<div id="schedule_div" class="progress progress-striped active" hidden>
		<div id="schedule" class="progress-bar progress-bar-success" role="progressbar"
		     aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
		     style="width: 0%;">
		</div>
	</div>
	<br>
	<button id="button_start" type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#startmodel">
		开始同步
	</button>
</div>


<!-- 确认开始同步弹出框 -->
<div class="modal modal-alert modal-warning fade" id="startmodel" tabindex="-1" role="dialog" aria-labelledby="startModalLabel">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<i class="fa fa-warning"></i>
			</div>
			<div class="modal-title"></div>
			<div class="modal-body">
				<span class=" text-info">确定要同步吗？</span>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn" data-dismiss="modal">取消</button>
				<button type="button" onclick="start();" class="btn btn-danger" data-dismiss="modal">确定</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

	var title = $('#imqywx_titile');

	function start() {
		$.ajax({
			'type': 'POST',
			'url': "{$department}",
			beforeSend: function () {
				// 隐藏按钮
				$('#button_start').hide('fast');
				// 显示进度条
				$('#schedule_div').show();
				title.html('同步部门中...');
			},
			success: function(c){
				if (c.errcode != 0) {
					title.html(c.errmsg);
					return false;
				}

				title.html('同步部门完成!');

				setTimeout(im_member(), 2000);
			},
			error: function(e){
				title.html('网络错误');
			}
		});

		return true;
	}

	function im_member() {
		$.ajax({
			'type': 'POST',
			'url': "{$member}",
			beforeSend: function () {
				title.html('同步人员中...');
			},
			success: function(c){
				if (c.errcode != 0 && c.errcode != 1) {
					title.html(c.errmsg);
					return false;
				}

				if (c.errcode == 1) {
					// 改变进度条长度
					$('#schedule').attr('style', 'width: ' + c.errmsg + '%');

					// 继续同步人员
					im_member();
				} else {
					// 改变进度条长度
					$('#schedule').attr('style', 'width: ' + 100 + '%');

					title.html('同步通讯录完成! (<span id="timeout">3</span>秒后跳转通讯录列表)');

					timeout(3);
				}
			},
			error: function(e){
				title.html('网络错误');
			}
		});

		return true;
	}

	// 倒数计时
	function timeout(time) {
		setInterval(function(){
			if(time == 0) {
				location.href = "{$list}";
			}
			$('#timeout').html(time--);
		},1000);
	}

</script>


{include file="$tpl_dir_base/footer.tpl"}