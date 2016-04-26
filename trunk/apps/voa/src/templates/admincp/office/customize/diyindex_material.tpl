<form class="form-inline padding-sm-vr no-padding-t text-right">
	<div class="form-group">
		<label class="sr-only" for="exampleInputEmail2"></label>
		<input type="text" class="form-control" placeholder="输入关键字">
	</div>
	<button type="submit" class="btn btn-primary">搜索</button>
</form>
<table class="table table-hover">
<thead>
<tr>
	<th>#</th>
	<th width="50%">标题</th>
	<th>创建时间</th>
	<th>操作</th>
</tr>
</thead>
<tbody>
{foreach $list as $_v}
<tr>
	<td>{$_v['tiid']}</td>
	<td class="text-left" id="material_name_{$_v['mtid']}">{$_v["subject"]}</td>
	<td>{$_v["_created"]}</td>
	<td>
		<button class="btn btn-info btn-sm relate" data-href="/frontend/travel/specialtopic?mtid={$_v['mtid']}" data-id="{$_v['mtid']}">关联</button>
	</td>
</tr>
{/foreach}
</tbody>
</table>
{$multi}