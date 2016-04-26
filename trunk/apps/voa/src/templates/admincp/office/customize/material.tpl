{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"> <strong>搜索文章</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" id="soform" role="form" method="get" action="{$material_url}">
		<input type="hidden" name="issearch" value="1" />
		<input type="hidden" name="orderby" id="orderby" value="{$orderby}" />
		<input type="hidden" name="so_date" id="so_date" value="{$so_date}" />
		<div class="form-row">
			<div class="form-group">
				<label class="vcy-label-none" for="id_p_subject">专题标题：</label>
				<input type="text" class="form-control form-small" name="subject" placeholder="标题" value="{$username|escape}" maxlength="30" />
			</div>
			<span class="space"></span>
			<div class="form-group">
				<label class="vcy-label-none" for="id_rb_time_after">更新时间：</label>
				<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
					<input type="text" class="input-sm form-control" name="start_date" placeholder="开始日期" value="{$start_date}" />
					<span class="input-group-addon">至</span>
					<input type="text" class="input-sm form-control" name="end_date" placeholder="结束日期" value="{$end_date}" />
				</div>
			</div>

			<span class="space"></span>
			<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"> <i class="fa fa-search"></i>
				搜索
			</button>

		</div>
		</form>
	</div>
</div>

<form class="form-inline vcy-from-search" id="soform" role="form" method="get" action="{$material_url}?act=del">
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表<a href="{$material_url}?act=update" class="pull-right btn btn-xs btn-primary">新增文章</a>
		</div>
	</div>
	<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5">
		<col class="t-col-25">
		<col class="t-col-10">
		<col class="t-col-10">
	</colgroup>
	<thead>
		<tr>
			<th class="text-left">
			<label class="checkbox">
				<input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this, 'mtid');">
				<span class="lbl">全选</span>
			</label>
			</th>
			<th>标题</th>
			<th>创建时间</th>
			<th>操作</th>
		</tr>
	</thead>
	{if !empty($multi)}
    <tfoot>
        <tr>
            <td colspan="4" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>
	{/if}
	<tbody>
	{foreach $list as $_id => $_v}
	<tr>
		<td>
			<label class="px-single">
				<input type="checkbox" name="mtid[]" value="{$_v['mtid']}" />
				<span class="lbl"></span>
			</label>
		</td>
		<td class="text-left"><a href="{$material_url}?act=update&mtid={$_v['mtid']}">{$_v['subject']|escape}</a></td>
		<td>{$_v['_updated']}</td>
		<td>
			<a href="{$material_url}?act=del&mtid={$_v['mtid']}" class="text-danger _delete"><i class="fa fa-times"></i>删除</a>
			|
			<a href="{$material_url}?act=update&mtid={$_v['mtid']}"><i class="fa fa-edit"></i>编辑</a>
		</td>
	</tr>
	{foreachelse}
    <tr>
        <td colspan="4" class="warning">{if $issearch}未搜索到指定条件的专题信息{else}暂无对应数据{/if}</td>
    </tr>
	{/foreach}
	</tbody>
	</table>
</div>
</form>

<script type="text/javascript">
$(function() {
	// 日期
	$('.input-daterange').datepicker({
		todayHighlight: true
	});
	
	// 全选
	$('#chkall').on('click', function(e) {
		var self = $(this);
		self.parents("form").find("input[name='mtid[]']").each(function(ipt) {
			$(this).prop('checked', self.prop('checked'));
		});
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
