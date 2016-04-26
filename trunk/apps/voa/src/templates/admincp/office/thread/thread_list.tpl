{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
				    <label class="vcy-label-none" for="id_begintime">发布时间：</label>
					<script>
						init.push(function () {	
							var options2 = {							
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range').datepicker(options2);
						});
					</script>					
					<div class="input-daterange input-group" style="width: 260px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<input type="text" class="input-sm form-control" id="starttime" name="starttime" value="{$searchBy['starttime']}"  placeholder="开始日期"  />
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="endtime" name="endtime" value="{$searchBy['endtime']}" placeholder="结束日期"  />
					</div>
				    
				    <span class="space" style="margin-left: 50px;"></span>
					<label class="vcy-label-none" for="id_cab_realname_author">发布人：</label>
					<input type="text" class="form-control form-small" id="id_username" name="username" value="{$searchBy['username']|escape}" placeholder="输入姓名"  maxlength="54" />		
				</div>
			</div>
			
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">话题标题：</label>
					<input type="text" class="form-control form-small" style="width: 260px;" id="id_subject" name="subject" value="{$searchBy['subject']|escape}" placeholder="输入标题"  maxlength="54" />
					
					<span class="space" style="margin-left: 50px;"></span>
					<label class="vcy-label-none" for="id_fp_type" style="width: 49px;text-align: right;">排序：</label>
					<select id="sort_type" name="sort_type" class="form-control form-small"  style="width: 194px;" data-width="auto">
						<option value="1" {if $searchBy['sort_type'] == 1} selected="selected"{/if}>默认排序</option>
						<option value="2" {if $searchBy['sort_type'] == 2} selected="selected"{/if}>按评论从高到低</option>
						<option value="3" {if $searchBy['sort_type'] == 3} selected="selected"{/if}>按评论从低到高</option>
						<option value="4" {if $searchBy['sort_type'] == 4} selected="selected"{/if}>按点赞从高到低</option>
						<option value="5" {if $searchBy['sort_type'] == 5} selected="selected"{/if}>按点赞从低到高</option>
					</select>
					
					<span class="space" style="margin-left: 50px;"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
			
			
		</form>
	</div>
</div>

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			列表
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>话题标题</th>
			<th>发布人</th>
			<th>类型</th>
			<th>发布时间</th>
			<th>评论</th>
			<th>赞</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$_data['subject']|escape}</td>
			<td>{$_data['username']|escape}</td>
			<td>{if $_data['good']}热门{/if}  {if $_data['choice']}精选{/if}</td>
			<!--<td>{$_data['_created']}</td>--><!--这是原来的写法，显示的格式是“昨天 18:40”-->
			<td>{$_data['__created']}</td>
			<td>{$_data['replies']|escape}</td>
			<td>{$_data['likes']|escape}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的话题信息{else}暂无任何话题信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}