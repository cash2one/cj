{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			<ul class="nav nav-pills text-sm">
				<li><a href="javascript:void(0);" id="open">启用</a></li>
				<li><a href="javascript:void(0);"  id="close"">禁用</a></li>
				<li><a href="javascript:void(0);"  id="delete_list">删除</a></li>
				<li><a href="{$addBaseUrl}"><i class="fa fa-plus"></i>&nbsp;添加流程</a></li>
			</ul>
		</div>
	</div>
	<div class="form-horizontal" id="action_form">

		<table class="table table-striped table-bordered table-hover font12" id="table_mul">
			<colgroup>
				<col class="t-col-5" />
				<col />
				<col class="t-col-12" />
				<col class="t-col-15" />
				<col class="t-col-20" />
				<col class="t-col-15" />
			</colgroup>
			<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');" /><span class="lbl">全选</span></label></th>
				<th>流程名称</th>
				<th>状态</th>
				<th>创建人</th>
				<th>创建时间</th>
				<th>操作</th>
			</tr>
			</thead>

			<tfoot id="tbody-page">

			</tfoot>

			<tbody id="tbody-list">

			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		var limit = 10;
		function _page(){

			$('#table_mul .pagination a').on('click', function (e) {
				e.preventDefault();
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				var data = {};
				data.page = result[1];
				data.limit = limit;
				_post(data);

			});

		}
		function _post(data){

			$.ajax({
				url: '/Askfor/Apicp/Template/List',
				dataType: 'json',
				data: data,
				type: 'get',
				success: function (result) {
					$('#tbody-list').html(txTpl('tpl-list', result.result));
					$('#tbody-page').html(txTpl('tpl-page', result.result));
					_page();
					return false;
				}
			});
		}
		$.ajax({
			url: '/Askfor/Apicp/Template/List',
			dataType: 'json',
			//jsonp: 'callback',
			data: 'limit=' + limit,
			type: 'get',
			success:function(result){
				if(result.errcode == 0){
					$('#tbody-list').html(txTpl('tpl-list', result.result));
					$('#tbody-page').html(txTpl('tpl-page', result.result));

					_page();
					$('#delete_list').on('click', function () {
						if (!confirm("确定删除吗？")) {
							return false;
						}
						var value = [];
						$('input[name="delete"]:checked').each(function () {
							value.push(parseInt($(this).val()));
						});

						var da = {}
								da.aft_id = value;
						$.ajax({
							url: '/Askfor/Apicp/Template/Delete',
							dataType: 'json',
							data: da,
							type: 'post',
							success: function () {
								alert('删除成功!');
								window.location.href = window.location.href;
							}
						});
					});

					$('.delete').on('click', function (e) {
						e.preventDefault();
						if (!confirm("确定删除吗？")) {
							return false;
						}
						$.ajax({
							url: '/Askfor/Apicp/Template/Delete',
							dataType: 'json',
							data: 'aft_id=' + $(this).attr('af-id'),
							type: 'post',
							success: function () {
								alert('删除成功!');
								window.location.href = window.location.href;
							}
						});
					});
					$('#open').on('click', function(){
						var value = [];
						$('input[name="delete"]:checked').each(function () {
							value.push(parseInt($(this).val()));
						});

						var da = {}
								da.aft_id = value;
						da.value = '1';
						$.ajax({
							url: '/Askfor/Apicp/Template/Open',
							dataType: 'json',
							data: da,
							type: 'post',
							success: function (result) {
								if(result.errcode == 0){
									alert('启用成功!');
									window.location.href = window.location.href;
								}else{
									alert('修改失败');
								}
							}
						});
					});
					$('#close').on('click', function(){
						var value = [];
						$('input[name="delete"]:checked').each(function () {
							value.push(parseInt($(this).val()));
						});
						var da = {}
								da.aft_id = value;
						da.value = '0';
						$.ajax({
							url: '/Askfor/Apicp/Template/Open',
							dataType: 'json',
							data: da,
							type: 'post',
							success: function () {
								if(result.errcode == 0){
									alert('禁用成功!');
									window.location.href = window.location.href;
								}else{
									alert('修改失败');
								}
							}
						});
					});
				}else{
					alert(result.errcode + ' : ' + result.errmsg);
				}
			}

		});
	});

</script>

<script type="text/template" id="tpl-list">
	<% if (!jQuery.isEmptyObject(list)) { %>
	<% $.each(list,function(n,val){ %>
	<tr>
		<td class="text-left"><label class="px-single"><input type="checkbox" name="delete" class="px" value="<%=val['aft_id']%>" /><span class="lbl"> </span></label></td>
		<td><%=val['name']%></td>
		<td><%=val['_is_use']%></td>
		<td><%=val['creator']%></td>
		<td><%=val['_created']%></td>
		<td>
			<a class="delete" af-id="<%=val['aft_id']%>" style="color:#337AB7;"><i class="fa fa-times" style="color:#337AB7;"></i>删除</a>
			|
			<a class="edit" href="{$editBaseUrl}?aft_id=<%=val['aft_id']%>" style="color:#337AB7;"><i class="fa fa-edit" style="color:#337AB7;"></i>编辑</a>
		</td>
	</tr>

	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="6" class="warning">暂无任何审批流程</td>
	</tr>
	<% } %>
</script>
<script type="text/template" id="tpl-page">
	<% if (total > 0) { %>
	<tr>
		<td colspan="6" class="text-right vcy-page"><%=multi%></td>
	</tr>
	<% } %>
</script>
{include file="$tpl_dir_base/footer.tpl"}
