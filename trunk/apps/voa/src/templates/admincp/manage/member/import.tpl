{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading"><strong>上传 Excel 员工通讯录</strong></div>
	<div class="panel-body">
		<form id="upload_file" class="form-inline vcy-from-search" role="form" method="POST" enctype="multipart/form-data" action="{$form_action_url}">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<div class="form-row">
				<div class="help-block font12">
					<span class="text-danger">
						<ul>
							<li>为避免导入出错，请使用下载的 Excel 模板，填写后上传，请勿更改模板格式与列排序;</li>
							<li>第一列标记为“#”则表示忽略该行数据的导入;</li>
							<li>部门信息为上下级的全部结构, 部门之间使用英文字符 <b>/</b> 隔开, 如: <b>第一级部门/第二级部门/第三级部门</b></li>
						</ul>
					</span>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label>默认部门</label>
					{$department_select}
				</div>
				<div class="form-group">
					<label class="vcy-label-none" for="id-upload">上传 Excel 通讯录</label>
					<input type="file" class="form-control font12" id="id-upload" name="upload" accept="application/msexcel" />
					<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> 提交上传</button>
					<a href="{$template_download_url}" class="btn btn-info"><i class="fa fa-download"></i> 员工信息 Excel 模板下载</a>
				</div>
			</div>
		</form>
		<div class="help-block">
			<div id="_operation_msg"></div>
		</div>
{if $action == 'report'}
		<ul class="nav nav-tabs font12">
			<li class="title">处理结果报告：</li>
			<li class="active"><a href="#ignore" data-toggle="tab">已忽略 {$ignore_count}条</a></li>
			<li><a href="#success" data-toggle="tab">已导入 {$success_count}条</a></li>
		</ul>
		<div class="tab-content">
			<br />
	{foreach $report_list as $type => $list}
			<div class="tab-pane{if $type == 'ignore'} active{/if}" id="{$type}">
		{if $type == 'ignore'}
				<form id="submit_change" role="form" method="POST" action="{$form_change_action_url}">
				<input type="hidden" name="formhash" value="{$formhash}" />
		{/if}
				<div class="table-responsive-weight">
					<table class="table table-striped table-hover font12" style="width:2000px;max-width:2000px">
						<colgroup>
							<col class="t-col-10" />
							<col class="t-col-2" />
							<col class="t-col-4" />
							<col class="t-col-6" />
							<col class="t-col-9" />
							<col class="t-col-6" />
							<col class="t-col-6" />
							<col class="t-col-4" />
							<col class="t-col-3" />
							<col class="t-col-5" />
							<col class="t-col-8" />
							<col class="t-col-3" />
							<col class="t-col-6" />
							<col class="t-col-5" />
							<col class="t-col-12" />
							<col />
						</colgroup>
						<thead>
							<tr>
								<th>处理结果</th>
								<th>忽略</th>
		{foreach $report_col_field_names as $_key => $_name}
								<th>{$_name}</th>
		{/foreach}
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="{$report_col_span}"></td>
							</tr>
						</tfoot>
						<tbody>
		{foreach $list as $tmp2 => $row}
							<tr>
									<td>{$row['_result_msg']|escape}</td>
			{if $type == 'ignore'}
									<td><input type="checkbox" name="ignore[{$row@iteration}]" value="1"{if stripos($row['_result_msg'], '忽略') !== false} checked="checked"{/if} /></td>
				{foreach $row as $tmp3 => $v}
					{if $tmp3 != '_result_msg'}
									<td><input type="text" name="new[{$row@iteration}][{$tmp3}]" value="{$v|escape}" class="form-control font12 form-small" /></td>
					{/if}
				{/foreach}
			{else}
									<td></td>
				{foreach $row as $tmp3 => $v}
									{if $tmp3 != '_result_msg'}<td>{$v|escape}</td>{/if}
				{/foreach}
			{/if}
							</tr>
		{foreachelse}
							<tr class="warning">
								<td colspan="{$report_col_span}">未读到此类数据</td>
							</tr>
		{/foreach}
						</tbody>
					</table>
				</div>
		{if $type == 'ignore'}
				<div class="text-right"><button type="submit" class="btn btn-primary">提交修改</button></div>
				</form>
		{/if}
			</div>
	{/foreach}
		</div>
{/if}
	</div>
</div>

<script type="text/html" id="post-progress">
<div class="row">
	<div class="col-sm-2"><strong id="post-info">正在导入：<span id="post-info-num"><%=num%></span> / <%=total%></strong></div>
	<div class="col-sm-9">
		<div class="progress progress-striped active">
			<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<%=value%>" aria-valuemin="0" aria-valuemax="100" style="width: <%=value%>%">
				<span class="sr-only2">已完成 <span id="post-info-p"><%=value%></span>%</span>
			</div>
		</div>
	</div>
</div>
</script>

<script type="text/javascript">
var loading_icon = '<img src="{$IMGDIR}loading.gif" alt="" />';
var msg_id = '_operation_msg';
var jq_msg = jQuery('#'+msg_id);
var post_address_book_url = '{$post_address_book_url}';
var report_list_url = '{$report_list_url}';
var _data_total = 0;
{literal}
function post_address_book(r, num) {
	if (jQuery.isEmptyObject(r) && num <= _data_total) {
		jQuery.ajax({
			"url":post_address_book_url,
			"data":{"num":num,"total":_data_total},
			"dataType":"json",
			"success":function(data){
				post_address_book(data.result, num+1);
			},
			"complete":function(){
				var p = num/_data_total;
				p = p.toFixed(2);
				p = p*100;
				jQuery('#post-info-num').text(num);
				jQuery('#post-info-p').text(p);
				jQuery('.progress-bar').attr('aria-valuenow', p).css('width', p+'%');
				if (num == _data_total) {
					jQuery('.progress').removeClass('active').removeClass('progress-striped');
					jQuery('#post-info').html('完成导入，即将进入报告页面……').css({'font-size':'12px','font-weight':'normal'});
					delayURL(report_list_url, 1);
				}
			}
		});
	}
}
jQuery(function(){

	jQuery('#upload_file').on('submit', function(e){
		jQuery(this).ajaxSubmit({
			"beforeSubmit":function(){
				jq_msg.html(loading_icon+" 正在上传分析数据，请稍候 ……");
			},
			"dataType":"json",
			"url":jQuery('#upload_file').attr('action')+'&ajax=1',
			"error":function(event, position, total, percentComplete) {
				jq_msg.html('上传通讯录发生错误，请重试');
			},
			"success":function(data){
				if (data.errcode) {
					jq_msg.empty();
					WG.popup({"title":"上传通讯录文件发生错误","content":data.errmsg});
				} else {
					jq_msg.html('数据分析完毕，即将导入通讯录信息，请稍候……');
					_data_total = data.result.total;
					var tpl_var = {
						"num":1,
						"total":_data_total,
						"value":0
					};
					jq_msg.html(txTpl('post-progress', tpl_var));
					post_address_book(null, data.result.num);
				}
			}
		});
		return false;
	});
	
	jQuery('#submit_change').on('submit', function(e){
		jQuery(this).ajaxSubmit({
			"beforeSubmit":function(){
				jq_msg.html(loading_icon+" 正在提交更改，请稍候……");
			},
			"dataType":"json",
			"url":jQuery('#submit_change').attr('action')+'&ajax=1',
			"error":function(event, position, total, percentComplete) {
				jq_msg.html('上传通讯录发生错误，请重试');
			},
			"success":function(data){
				if (data.errcode) {
					jq_msg.empty();
					WG.popup({"title":"上传通讯录文件发生错误","content":data.errmsg});
				} else {
					jq_msg.html('数据分析完毕，即将导入通讯录信息，请稍候……');
					_data_total = data.result.total;
					var tpl_var = {
						"num":1,
						"total":_data_total,
						"value":0
					};
					jq_msg.html(txTpl('post-progress', tpl_var));
					post_address_book(null, data.result.num);
				}
			}
		});
		return false;
	});
});
{/literal}
</script>

{include file="$tpl_dir_base/footer.tpl"}