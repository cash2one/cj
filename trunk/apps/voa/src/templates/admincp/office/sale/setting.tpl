{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">

	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>自定义设置</strong></h3>
	</div>
	<form id="sub" class="form-horizontal" role="form" method="post">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="panel-body">
		<button type="button" class="btn _add" data-toggle="modal" data-target="#modal-sizes-1" >新增</button>
		<hr />
		<ul class="nav nav-tabs nav-tabs-xs">
			<li {if $type == 1}class="active"{/if}>
				<a id="id_fileds" href="#dashboard-recent-comments" data-toggle="tab">客户详情</a>
			</li>
			<li {if $type == 2}class="active"{/if}>
				<a id="id_type" href="#dashboard-recent-threads" data-toggle="tab">客户状态</a>
			</li>
			<li {if $type == 3}class="active"{/if}>
				<a id="id_source" href="#dashboard-recent-threads" data-toggle="tab">客户来源</a>
			</li>
		</ul>
		<table class="table table-bordered table-hover font12">
			<colgroup>
				<col class="t-col-35" />
				<col class="t-col-35" />
				<col class="t-col-30" />
			</colgroup>
			<thead>
				<tr>
					<th>字段参数</th>
					<th>更新时间</th>
					<th>操作</th>
				</tr>
			</thead>
		{if $type == 1}
		<tr>
			<td>简称</td>
			<td>--</td>
			<td>--</td>
		</tr>
		<tr>
			<td>全程</td>
			<td>--</td>
			<td>--</td>
		</tr>
		<tr>
			<td>地址</td>
			<td>--</td>
			<td>--</td>
		</tr>
		<tr>
			<td>联系人</td>
			<td>--</td>
			<td>--</td>
		</tr>
		<tr>
			<td>联系方式</td>
			<td>--</td>
			<td>--</td>
		</tr>
		{/if}
		{if $total > 0}
			<tfoot>
				<tr>
					<td colspan="3" class="text-right vcy-page">{$multi}</td>
				</tr>
			</tfoot>
		{/if}
			<tbody>
		{foreach $list as $_id => $_data}
				<tr>
					<td>{$_data['name']|escape}</td>
					<td>{$_data['updated']|escape}</td>
					<td><button type="button" class="btn _modify" data-toggle="modal" data-target="#modal-sizes-1" data-stid="{$_data['stid']}" data-color="{$_data['color']|escape}"  data-name="{$_data['name']|escape}">修改</button>&nbsp;&nbsp;<input type="button" class="btn _delete" data-stid="{$_data['stid']}" value="删除"/></td>
				</tr>
		{foreachelse}
				<tr>
					<td colspan="3" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{/if}</td>
				</tr>
		{/foreach}
			</tbody>
		</table>
		<input type="hidden" id="type" name="type" value="1"/>
		</form>
	</div>
</div>
<form id="sub-dialog" class="form-horizontal" role="form" method="post">
	<div id="modal-sizes-1" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">字段参数</h4>
				</div>
				<div class="modal-body">
					<label>参数名：</label>
					<input type="text" id="field" name="field" value="" />
					{if $type == 1}
					<br />
					<label for="required">必填</label>
					<input type="checkbox" name="required" id="required" value="0">
					<br />
					{/if}
					{if $type == 2}
					<label>色块设置：</label>
					<input type="color" id="color" name="color" id="required" value="" /><br />
					{/if}
					<input type="hidden" id="stid" name="stid" value="" />
					<input type="hidden" id="dstid" name="dstid" value="" />
					<input type="hidden" id="types" name="types" value="{$type}"/>
					<button type="button" class="btn _sub" >提交</button>
				</div>
			</div> 
		</div> 
	</div>
</form>

<script type="text/javascript">
jQuery(function () {
	$('#required').on('click', function() {
		if (this.checked) {
			$(this).val(1);
		} else {
			$(this).val(0);
		}
	});

	jQuery('._modify').click(function () {
		var jq_t = jQuery(this);
		var id = jq_t.attr('data-stid');
		var name = jq_t.attr('data-name');
		var color = jq_t.attr('data-color');
		
		jQuery('#stid').val(id);
		jQuery('#field').val(name);
		jQuery('#color').val(color);
		jQuery('#dstid').val('');
	});
	
	jQuery('._add').click(function () {
		jQuery('#stid').val('');
		jQuery('#field').val('');
		jQuery('#dstid').val('');
	});
	
	jQuery('._sub').click(function () {
		if(jQuery('#field').val() == ''){
			alert("参数名不能为空！");
			return false;
		}
		jQuery('#sub-dialog').submit();
	});
	
	jQuery('._delete').click(function () {
		if(confirm("确认删除吗？")) {
			var jq_t = jQuery(this);
			var id = jq_t.attr('data-stid');
			jQuery('#dstid').val(id);
			jQuery('#sub-dialog').submit();
		}
	});
	
	jQuery('#id_fileds').click(function () {
		
		change_type(1);
	
	});
	jQuery('#id_type').click(function(){
		
		change_type(2);
	
	});
	jQuery('#id_source').click(function(){
		
		change_type(3);
	
	});
	function change_type(type1) {
		jQuery('#type').val(type1);
		jQuery('#sub').submit();
	}
	
});
	function onModified(id,value){
		jQuery('#stid').val(id);
		jQuery('#fildess').val(value);
	}
</script>


{include file="$tpl_dir_base/footer.tpl"}