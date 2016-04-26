{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索文章</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_ao_username">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;作者：</label>
					<input type="text" class="form-control form-small" id="id_author" name="author"  value="{$searchBy['author']|escape}" maxlength="54" />
					<span class="space"></span>					
					
					<label class="vcy-label-none" for="id_ao_begintime">标题：</label>
					<input type="text" class="form-control form-small" id="id_title" name="title"  value="{$searchBy['title']|escape}" maxlength="54" />
					
					<span class="space"></span>
					<label class="vcy-label-none" for="id_ao_subject">目录：</label>
					{if count($categories)>0}
					<select id="id_tc_id" name="tc_id" class="form-control form-small" data-width="auto">
						<option value="-1">所有目录</option>
							{foreach $categories as $_key => $_name}
								<option value="{$_key}"{if $searchBy['tc_id'] == $_key} selected="selected"{/if}>{$_name['title']|escape}</option>
							{/foreach}
					</select>
					{/if}
					
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<script>
						init.push(function () {	
							var options2 = {							
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options2);
							$('#bs-datepicker-range2').datepicker(options2);
						});
					</script>	
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">创建时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_created_begintime" name="created_begintime"   placeholder="开始日期" value="{$searchBy['created_begintime']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_created_endtime" name="created_endtime" placeholder="结束日期" value="{$searchBy['created_endtime']|escape}" />
						</div>
					</div>
					<span class="space"></span>
					<div class="input-daterange input-group" style="width:290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range2">
						<label class="vcy-label-none" for="id_updated">更新时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_updated_begintime" name="updated_begintime"   placeholder="开始日期" value="{$searchBy['updated_begintime']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_updated_endtime" name="updated_endtime" placeholder="结束日期" value="{$searchBy['updated_endtime']|escape}" />
						</div>
					</div>				
					
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a class="btn btn-default form-small form-small-btn" role="button" href="{$listAllUrl}">全部记录</a>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
<form class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
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
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th class="text-left">标题</th>
			<th>目录位置</th>
			<th>作者</th>
			<th>创建/更新时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2" class="text-left">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="4" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td class="text-left">{$_data['title']|escape}</td>
			<td>{$_data['category']|escape}</td>			
			<td>{$_data['author']|escape}</td>
			<td>{$_data['created']}<br />{$_data['updated']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($editUrlBase, $_id, '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="6" class="warning">{if $issearch}未搜索到指定条件的文章{else}暂无任何文章{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}
