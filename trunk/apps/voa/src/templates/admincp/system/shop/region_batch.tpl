
<div class="panel panel-default">
	<div class="panel-heading"><strong>一. 模板下载</strong></div>
	<div class="panel-body">
		<ol>
			<li><a href="{$download_tpl_url}" class="btn btn-info">下载模板文件</a></li>
			<li>按照模板要求填写区域对应信息。</li>
			<li>请按照数据模板的格式准备导入数据，模板中的表头不可作任何修改，否则将会无法正常导入。</li>
			<li>区域级别暂时设为三个级别，区域级别至少需要一级</li>
			<li>实际填写时，请填写每个区域的所涉及的所有区域</li>
		</ol>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading"><span class="pull-right"><a href="javascript:;" onclick="javascript:_reset_import();"><i class="fa fa-refresh"></i> 重新导入</a></span><strong>二. 选择需要导入的 Excel 文件</strong></div>
	<div class="panel-body">
		<form class="form-horizontal" role="form" action="{$form_add_batch_url}" method="post" enctype="multipart/form-data">
		<input type="hidden" name="formhash" value="{$formhash}" />
			<div class="form-group" id="id-fileupload-area">
				<input id="fileupload" type="file" name="upload" data-url="{$form_add_batch_url}" class="form-control" placeholder="选择 execle 文件" required="required" accept="application/vnd.ms-excel" />
			</div>
			<div id="upload-operation" style="display: none">
				<div id="upload-result"></div>
				<div id="progress" class="progress">
					<div class="bar-upload progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="50" style="width: 0%">
					</div>
					<div class="bar-import progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="50" style="width: 0%">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div id="import-report"></div>

<script type="text/javascript">

var count_success = 0;
var count_failed = 0;

/**
 * 重新导入
 */
function _reset_import() {
	count_success = 0;
	count_failed = 0;
	jQuery('#progress .bar-upload').css('width', '0%');
	jQuery('#progress .bar-import').css('width', '0%');
	jQuery('#id-fileupload-area').show();
	jQuery('#import-report').html('');
	_set_tip('');
}

/**
 * 设置操作提示信息
 */
function _set_tip(msg, is_loading) {
	if (typeof(is_loading) != 'undefined') {
		msg = '<img src="{$IMGDIR}loading.gif" alt="loading..." />' + msg;
	}
	jQuery('#upload-result').html(msg);
}

/**
 * 设置待处理数和进度条
 */
function _change_operation_count(total, o_count) {
	var s_count = total - o_count;
	var p_rate = Number((o_count/total)/2);
	p_rate = p_rate.toFixed(2) * 100;
	jQuery('#id-count').text(s_count);
	jQuery('#progress .bar-import').css('width', p_rate + '%');
	if (total == o_count) {
		// 导入完成
		_set_tip('已完成批量导入，成功导入 '+count_success+' 条数据，导入失败 '+count_failed+' 条，请检查下方的导入报告。');
		jQuery('#progress').hide();
		if (count_failed > 0) {
			jQuery('#id-btn').show();
			jQuery('#form-resubmit').submit(function(){
				_resubmit();
				return false;
			});
		}
	}
}

/**
 * 追加一行数据
 */
function _set_row(id, data, msg, success) {
	jQuery('#import-list').append(txTpl('tpl-import-list', {
		"id": id,
		"level_1": WG.filterXSS(data.level_1),
		"master_1": WG.filterXSS(data.level_1_master),
		"level_2": WG.filterXSS(data.level_2),
		"master_2": WG.filterXSS(data.level_2_master),
		"level_3": WG.filterXSS(data.level_3),
		"master_3": WG.filterXSS(data.level_3_master),
		"msg": msg,
		"success": typeof(success) == 'undefined' ? false : success
	}));
}

/**
 * 提交修改过的失败数据
 */
function _resubmit() {

	//console.log(jQuery('#form-resubmit').serialize());

	jQuery.ajax('{$batch_submit_url}', {
		"type" : "POST",
		"data" : jQuery('#form-resubmit').serialize(),
		"dataType" : "json",
		"beforeSend" : function(){
			_reset_import();
			_set_tip('正在上传中，请稍候 ...', true);
			jQuery('#upload-operation').fadeIn('normal');
		},
		"success" : function(r){
			if (typeof(r.errcode) == 'undefined') {
				_set_tip('获取批量区域信息发生错误');
				return false;
			}
			if (r.errcode != 0) {
				_set_tip(r.errmsg + '[Err: ' + r.errcode + ']');
				return false;
			}
			var result = r.result;

			// 进行批量导入
			_batch_import(result);
		}
	});

}

/**
 * 批量导入
 */
function _batch_import(result) {

	jQuery('#id-fileupload-area').hide();

	_set_tip('共 '+ result.total +' 条区域数据，尚有 <strong id="id-count">'+ result.total +'</strong> 条待导入的区域数据，正在处理中请稍候……');
	jQuery('#import-report').append(txTpl('tpl-result-list', result));

	var _o_count = 0;
	jQuery.each(result.data_list, function(i, data) {
		//console.log(data);
		count_failed++;
		jQuery.ajax({
			"url": "{$batch_import_url}",
			"type": "POST",
			"dataType": "json",
			"data": data,
			"error": function(XmlHttpRequest, textStatus, errorThrown) {
				return false;
			},
			"complete": function (XMLHttpRequest, textStatus) {
				_o_count++;
				_change_operation_count(result.total, _o_count);
			},
			"success": function(r) {
				if (typeof(r.errcode) === 'undefined') {
					_set_row(i, data, '未知解析错误');
					return false;
				}
				if (r.errcode > 0) {
					_set_row(i, data, r.errmsg);
					return false;
				}
				// 导入成功
				_set_row(i, data, '导入成功', true);
				count_success++;
				count_failed--;
			}
		});
	});
}

/**
 * 上传并导入Excel文件
 */
function _upload_excel() {
	jQuery('#fileupload').fileupload({
		"dataType": "json",
		"limitMultiFileUploads": 1,
		"autoUpload": true,
		"submit": function() {
			_set_tip('正在上传中，请稍候 ...', true);
			jQuery('#upload-operation').fadeIn('normal');
		},
		"progressall": function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			jQuery('#progress .bar-upload').css('width', Number(progress/2).toFixed(2) + '%');
		},
		"done": function (e, data) {
			if (typeof(data.result) == 'undefined') {
				_set_tip('上传文件发生错误.');
				return false;
			}
			var r = data.result;
			if (typeof(r.errcode) == 'undefined') {
				_set_tip('获取上传文件信息发生错误');
				return false;
			}
			if (r.errcode != 0) {
				_set_tip(r.errmsg + '[Err: ' + r.errcode + ']');
				return false;
			}
			var result = r.result;

			// 进行批量导入
			_batch_import(result);
		}
	});
}


// 绑定动作执行
jQuery(function(){
	_upload_excel();
});
</script>
<script type="text/template" id="tpl-result-list">
<form id="form-resubmit" onsubmit="javascript:return false;">
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
<% for (i = 0; i < field_name.length; i++) { %>
	<% var tmp = field_name[i]; %>
	<% if (tmp['key'] != key_ignore && tmp['key'] != key_result) { %>
		<col class="t-col-<%=tmp['width']%>" />
	<% } %>
<% } %>
	</colgroup>
	<thead>
		<tr>
<% for (i = 0; i < field_name.length; i++) { %>
	<% var tmp = field_name[i]; %>
	<% if (tmp['key'] != key_ignore && tmp['key'] != key_result) { %>
			<th><%=tmp['name']%></th>
	<% } %>
<% } %>
		</tr>
	</thead>
	<tfoot id="id-btn" style="display:none">
		<td colspan="<%=field_name.length%>" class="text-right"><button type="submit" id="resubmit" class="btn btn-info">再次提交</button></td>
	</tfoot>
	<tbody id="import-list">
	</tbody>
</table>
</form>
</script>

<script type="text/template" id="tpl-import-list">
<tr>
<% if (success) { %>
	<td>-</td>
	<td><%=level_1%></td>
	<td><%=master_1%></td>
	<td><%=level_2%></td>
	<td><%=master_2%></td>
	<td><%=level_3%></td>
	<td><%=master_3%></td>
	<td><%=msg%></td>
<% } else { %>
	<td><input type="checkbox" name="ignore[<%=id%>]" value="1" /></td>
	<td><input type="text" name="level_1[<%=id%>]" class="form-control" placeholder="一级区域名称" value="<%=level_1%>" /></td>
	<td><input type="text" name="level_1_master[<%=id%>]" class="form-control" placeholder="一级负责人" value="<%=master_1%>" /></td>
	<td><input type="text" name="level_2[<%=id%>]" class="form-control" placeholder="二级区域名称" value="<%=level_2%>" /></td>
	<td><input type="text" name="level_2_master[<%=id%>]" class="form-control" placeholder="二级负责人" value="<%=master_2%>" /></td>
	<td><input type="text" name="level_3[<%=id%>]" class="form-control" placeholder="三级区域名称" value="<%=level_3%>" /></td>
	<td><input type="text" name="level_3_master[<%=id%>]" class="form-control" placeholder="三级负责人" value="<%=master_3%>" /></td>
	<td><%=msg%></td>
<% } %>
</tr>
</script>

