{include file="$tpl_dir_base/header.tpl"}

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$module_plugin['cp_name']|escape}：{$minutes['mi_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
				<dt>记录人：</dt>
				<dd>
					<strong class="label label-primary font12">{$minutes['_realname']|escape}</strong>
					&nbsp;&nbsp;
					<abbr title="申请时间"><span class="badge">{$minutes['_created']}</span></abbr>
				</dd>
{if $mem_list}
				<dt>参会人：</dt>
				<dd>
	{foreach $mem_list as $mem}
					<span class="label label-info font12">{$mem['_realname']}</span>
					
	{/foreach}
				</dd>
{/if}
				<dt>会议主题信息：</dt>
				<dd>
					<blockquote class="m-0 font12">
						<h3 class="font12 text-bold"><strong>{$minutes['mi_subject']|escape}</strong></h3>
						{$minutes['_message']}
					</blockquote>
{if $attach_list}
				<div class="row">
	{foreach $attach_list as $_at}
					<div class="col-xs-2">
						<a href="{$_at['url']}" target="_blank" class="thumbnail"><img src="{$_at['thumb']}" border="0" alt="" /></a>
					</div>
	{/foreach}
				</div>
{/if}
				</dd>
			</dl>
		</div>
	</div>
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_comment" data-toggle="tab">
						<span class="badge pull-right"> {$post_count} </span>
						评论/回复&nbsp;
					</a>
				</li>
			</ul>
		
			<div class="tab-content">
				<div class="tab-pane active" id="list_comment">
					<table class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-20" />
							<col />
						</colgroup>
						<thead>
							<tr>
								<th>发言人</th>
								<th>内容</th>
							</tr>
						</thead>
						
						<tbody>
{foreach $post_list as $_aopt_id => $_aopt}
							<tr>
								<td>{$_aopt['m_username']|escape}<span class="help-block">{$_aopt['_created']}</span></td>
								<td>{$_aopt['_message']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="2">暂无发言信息</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
			</div>



{include file="$tpl_dir_base/footer.tpl"}