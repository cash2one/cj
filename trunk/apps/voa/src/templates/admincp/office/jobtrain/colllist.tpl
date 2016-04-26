{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>基本信息</strong></h3>
	</div>

	<div class="panel-body">
		<div class="form-horizontal font12" role="form">
	
			<div class="form-group">
				<label class="control-label col-sm-2">标题：</label>
				<div class=" col-sm-9 help-block">{$article['title']}</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">创建人：</label>
				<div class=" col-sm-9 help-block">{$article['m_username']}</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">最后更新时间：</label>
				<div class=" col-sm-9 help-block">{rgmdate($article['updated'], 'Y/m/d H:i')}</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-9">
					<div class="row">
						<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<ul class="nav nav-tabs font12">
	<li class="active">
		<a href="#" data-toggle="tab">
			<span class="badge pull-right"> {$total} </span>
			收藏人数&nbsp;
		</a>
	</li>
	<li class="pull-right">
		<button class="btn btn-info form-small form-small-btn margin-left-12" onclick="window.location.href='{$coll_export_url}';">导出</button>
	</li>
</ul>
<br />



<div class="table-light">
	<table class="table table-striped table-hover table-bordered font12">
		<colgroup>
			<col class="t-col-10" />
			<col class="t-col-10 "/>
			<col class="t-col-20" />
			<col class="t-col-20" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
		</colgroup>
		<thead>
			<tr>
				<th>姓名</th>
				<th>部门</th>
				<th>职位</th>
				<th>手机</th>
				<th>收藏时间</th>
			</tr>
		</thead>
	{if !empty($list)}
		<tbody>
		{foreach $list as $_id => $_data}
			<tr>
				<td>{$_data['m_username']}</td>
				<td>{$_data['department']}</td>		
				<td>{$_data['job']}</td>
				<td>{$_data['mobile']}</td>
				<td>{rgmdate($_data['created'], 'Y/m/d H:i')}</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="8" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{else}
		<tbody>
			<tr>
				<td colspan="10" class="warning">暂无任何数据</td>
			</tr>
		</tbody>
	{/if}
	</table>
</div>


{include file="$tpl_dir_base/footer.tpl"}