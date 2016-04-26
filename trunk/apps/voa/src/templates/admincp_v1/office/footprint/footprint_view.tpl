{include file='admincp/header.tpl'}

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$module_plugin['cp_name']|escape}：{$footprint['_realname']} 创建于：{$footprint['_created']}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>轨迹提交者：</dt>
				<dd>
					<strong class="label label-primary font12">{$footprint['_realname']|escape}</strong>
					<span class="space"></span>
					<abbr title="提交时间"><span class="badge">{$footprint['_created']}</span></abbr>
					<span class="space"></span>
					<span class="label label-primary font12">{$footprint['_department']} {$footprint['_job']}</span>
				</dd>
				<dt>客户名称：</dt>
				<dd>{$footprint['fp_subject']|escape}</dd>
				<dt>拜访时间：</dt>
				<dd>{$footprint['_visittime']}</dd>
				<dt>分类：</dt>
				<dd>{$footprint['_type']|escape}</dd>
				<dt>所在位置：</dt>
				<dd>{$footprint['_address']}</dd>
				<dt>分享给：</dt>
				<dd>
	{foreach $mem_list as $mem}
		{if $mem['m_uid'] != $footprint['m_uid']}
					<span class="label label-info font12">{$mem['_realname']}（{$mem['_department']|escape} {$mem['_job']}）</span>
		{/if}
	{foreachelse}
					无
	{/foreach}
				</dd>
			</dl>
			<br /><br />
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_comment" data-toggle="tab">
						<span class="badge pull-right"> {$attach_count} </span>
						附件列表&nbsp;
					</a>
				</li>
			</ul>
			<br />
			<div class="tab-content">
				<div class="tab-pane active" id="list_comment">
{include file='admincp/common_attachment_list.tpl'}
				</div>
			</div>
		</div>
	</div>

{include file='admincp/footer.tpl'}