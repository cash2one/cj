{include file="$tpl_dir_base/header.tpl"}

<div>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a id="already_tab" href="#already" aria-controls="home" role="tab" data-toggle="tab">已填人员</a></li>
		<li role="presentation"><a id="no_tab" href="#no" aria-controls="profile" role="tab" data-toggle="tab">未填人员</a></li>

		<span id="no_li" hidden>
			<a class="btn btn-warning" href="/Questionnaire/Apicp/Record/Export_nofill?qu_id={$qu_id}">导出未填人员数据</a>
			<li class="btn btn-success" onclick="send_message()" style="margin-right: 10px;">未填人员提醒</li>
		</span>

		<span id="already_li">
			<a class="btn btn-warning" href="/Questionnaire/Apicp/Record/Export_fill?qu_id={$qu_id}">导出填写数据</a>
		</span>

		<div class="btn btn-default pull-right" onclick="javascript:history.go(-1)">返 回</div>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		{*已经填写的人员列表*}
		<div role="tabpanel" class="tab-pane active" id="already">

			<table class="table table-striped table-hover table-bordered font12">
				<colgroup>
					<col class="t-col-10" />
					<col class="t-col-20" />
					<col class="t-col-20" />
					<col class="t-col-20" />
					<col class="t-col-20" />
				</colgroup>
				<thead>
				<tr>
					<th>序号</th>
					<th>姓名</th>
					<th>用户类型</th>
					<th>填写时间</th>
					<th>操作</th>
				</tr>
				</thead>
				<tbody id="already_list">

				</tbody>

				<tfoot id="already_list_multi">

				</tfoot>
			</table>

		</div>

		{*未填写的人员列表*}
		<div role="tabpanel" class="tab-pane" id="no">

			<table class="table table-striped table-hover table-bordered font12">
				<colgroup>
					<col class="t-col-10" />
					<col class="t-col-20" />
					<col class="t-col-20" />
					<col class="t-col-20" />
				</colgroup>
				<thead>
				<tr>
					<th>序号</th>
					<th>姓名</th>
					<th>手机</th>
					<th>邮箱</th>
				</tr>
				</thead>
				<tbody id="no_list">

				</tbody>

				<tfoot id="no_list_multi">

				</tfoot>
			</table>

		</div>
	</div>

</div>

<script type="text/template" id="tpl_already_list">

	<% if (!jQuery.isEmptyObject(list)) { %>

	<% $.each(list,function(k,val){ %>
	<tr>
		<td><%=val['number']%></td>
		<td><%=val['username']%></td>
		<td><%=val['from']%></td>
		<td><%=val['created']%></td>
		<td><%=val['operation']%></td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="5" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>
</script>

<script type="text/template" id="tpl_already_list_multi">

	<tr>
		<td colspan="10" class="text-right">
			<% if(multi != null){ %>
			<%=multi%>
			<% } %>
		</td>
	</tr>

</script>

<script type="text/template" id="tpl_no_list">

	<% if (!jQuery.isEmptyObject(list)) { %>

	<% $.each(list,function(k,val){ %>
	<tr>
		<td><%=val['number']%></td>
		<td><%=val['username']%></td>
		<td><%=val['phone']%></td>
		<td><%=val['email']%></td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="4" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>
</script>

<script type="text/template" id="tpl_no_list_multi">

	<tr>
		<td colspan="10" class="text-right">
			<% if(multi != null){ %>
			<%=multi%>
			<% } %>
		</td>
	</tr>

</script>

<script type="text/template" id="tpl_see">

	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="row">
					<b class="col-md-4"><%=title%></b>
					<div class="col-md-4"><%=classify%></div>
					<div class="col-md-4"><%=created%></div>
				</div>
			</div>
			<div class="modal-body">
				<%=body%>
				<hr />
				<dl class="dl-horizontal">
					<% $.each(view,function(k,val){ %>
					<dt>
						<%=val.title%>
					</dt>

					<dd>
					<% if (val.type == 'text' || val.type == 'textarea' || val.type == 'address' || val.type == 'email' || val.type == 'number' || val.type == 'date' || val.type == 'time' || val.type == 'datetime' || val.type == 'score' || val.type == 'note' || val.type == 'mobile' || val.type == 'username') { %>
					<%=val.value%>
					<% } else if (val.type == 'radio' || val.type == 'select' || val.type == 'checkbox') { %>
						<% $.each(val.option, function(k,val) { %>
							<% if (val.selected == true) { %>
								<%=val.value%>
								<% if (val.other == "true" || val.other == true) { %>
									( <%=val.other_value%> )
							<% } %>
							;
							<% } %>
						<% }) %>
					<% } else if (val.type == 'image') { %>
						<% $.each(val.value, function(k,val) { %>
							<a href="<%=val%>" target="_blank"><img src="<%=val%>" alt="" width="50" height="50"></a>
						<% }) %>
					<% } else if (val.type == 'file') { %>
						<a href="<%=val.url%>" target="_blank"><%=val.value%></a>
					<% } %>
					</dd>

					<% }) %>
				</dl>
				</table>
			</div>
		</div>
	</div>

</script>

<script>

	$(function () {

		// 读取已填写列表
		load_fillin();
		$('#already_tab').on('click', function () {
			load_fillin();

			$('#no_li').hide();
			$('#already_li').show();
		});

		// 读取未填写列表
		load_nofillin();
		$('#no_tab').on('click', function () {
			load_nofillin();

			$('#already_li').hide();
			$('#no_li').show();
		});
	});

	// 加载已填写人员列表
	function load_fillin() {

		// 加载已填人员
		var already_list_data = {
			qu_id: {$qu_id},
			fill_in: 1 // 已填人员
		};
		_list('/Questionnaire/Apicp/Record/List', ['already_list_multi', 'already_list'], already_list_data);
	}

	// 加载未填写人员列表
	function load_nofillin() {

		var no_list_data = {
			qu_id: {$qu_id},
			fill_in: 2 // 未填人员
		};
		_list('/Questionnaire/Apicp/Record/List?', ['no_list_multi', 'no_list'], no_list_data);
	}

	// 问卷填写详情数据
	function _see_tpl(id) {

		$.ajax({
			url: '/Questionnaire/Apicp/Record/View_answer',
			dataType: 'json',
			data: {
				qr_id: id
			},
			type: 'GET',
			success: function (result) {
				if (result.errcode != 0) {
					alert(result.errmsg);
					return false;
				}
				$('#see').html(txTpl('tpl_see', result.result));
			},
			error: function () {
				alert('网络错误');
				return false;
			}
		});
	}

	// 绑定点击事件
	function bind_click() {
		$('#already_list span').on('click', function () {
			var id = $(this).prop('id');
			var act = $(this).attr('act');
			if (id == '' || act == '') {
				return false;
			}
			// 页面暂存ID
			$('#tmp_id').attr('val', id);

			switch (act) {
				case 'see':
					// 查询填写情况
					_see_tpl(id);
					$('#see').modal('show');
					break;
				case 'del':
					$('#confirmModal').modal('show');
					break;
			}
		});
	}

	// 删除记录操作
	function act_del() {

		var act_id = $('#tmp_id').attr('val');
		if (act_id == '') {
			return false;
		}

		$.ajax({
			url: '/Questionnaire/Apicp/Record/Del',
			dataType: 'json',
			data: {
				qr_id: act_id
			},
			type: 'POST',
			success: function (result) {
				if (result.errcode != 0) {
					alert(result.errmsg);
					return false;
				}
			},
			error: function () {
				alert('网络错误');
				return false;
			}
		});

		sleep(500);

		// 加载已填人员列表
		load_fillin();
		// 加载未填写人员列表
		load_nofillin();
	}

	function sleep(milliseconds) {
		var start = new Date().getTime();
		for (var i = 0; i < 1e7; i++) {
			if ((new Date().getTime() - start) > milliseconds){
				break;
			}
		}
	}

	// 未填人员提醒
	function send_message() {
		var qu_id = "{$qu_id}";

		$.ajax({
			url: '/Questionnaire/Apicp/Record/questionnaireSend',
			dataType: 'json',
			data: {
				qu_id: qu_id
			},
			type: 'POST',
			success: function (result) {
				if (result.errcode != 0) {
					alert(result.errmsg);
					return false;
				} else {
					alert('发送成功');
				}
			},
			error: function () {
				alert('网络错误');
				return false;
			}
		});
	}

	/**
	 * 获取列表数据 和 分页数据
	 * @param url
	 * @param id ['分页模板外面的DIV ID', '']
	 * @param data {} 要给接口的参数数据
	 * @private
	 */
	function _list(url, id, data, except_select) {

		var except = except_select ? except_select : false;

		$.ajax({
			url: url,
			dataType: 'json',
			data: data,
			success: function (result) {
				for (var i = 0; i < id.length; i++) {
					// 是否需要跳过select的
					if (except) {
						if (id[i].substr(-7) == '_select') {
							continue;
						}
					}
					$('#' + id[i]).html(txTpl('tpl_' + id[i], result.result));
				}
				// 绑定分页参数点击事件
				_multi(url, id, data);
				// 绑定点击操作事件
				bind_click();
			}
		});
	}

	// 绑定点击事件
	function _multi(url, id, data) {

		$('#' + id[0] + ' a').on('click', function () {
			var page = 'page';
			var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
			if (result == null || result.length < 1) {
				return false;
			}
			data.page = result[1]; // 页数
			// 获取列表数据
			_list(url, id, data);
			return false;
		});

		return true;
	}
</script>

<!-- Modal -->
<div class="modal fade" id="see" tabindex="-1" role="dialog" aria-labelledby="seeLabel">

</div>

<div class="modal modal-alert modal-danger fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<i class="fa fa-warning"></i>
			</div>
			<div class="modal-title"></div>
			<div class="modal-body">
				<span class=" text-info">确定要删除吗？</span>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn" data-dismiss="modal">取消</button>
				<button type="button" class="btn btn-delete" onclick="act_del();" data-dismiss="modal">确定</button>
				<input type="hidden" name="delete-qc-id"/>
			</div>
		</div>
	</div>
</div>

<span id="tmp_id" val="" hidden>

{include file="$tpl_dir_base/footer.tpl"}