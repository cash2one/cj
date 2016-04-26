{include file="$tpl_dir_base/header.tpl"}

{if $multi}
	<div class="table-light">
		<div class="table-header">
			<div class="table-caption">
				正在安装套件应用：{$ordernum} / {$total_num}
			</div>
		</div>
	</div>
{else}
	<div class="table-light">
		<div class="table-header">
			<div class="table-caption">
			以下是您已授权的应用列表，请选择要与畅移云工作 <span class="text-danger" style="font-size:17px">“{$plugin['cp_name']|escape}”</span> 进行绑定的授权应用。
			如不需要操作，请点击 <a href="javascript:history.go(-1);" class="btn btn-default">返回</a>
			</div>
		</div>
		<br />
		<div class="row">
	{foreach $agent_list as $agent}
			<div class="col-sm-2 col-md-2">
				<div class="thumbnail text-center">
					<img src="{$agent['square_logo_url']}" width="99%" alt="{$agent['name']|escape}" />
					<div class="caption">
						<h3 style="font-size:17px">{$agent['name']}</h3>
		{if isset($agent_plugin_list[$agent['agentid']]) && !empty($agent_plugin_list[$agent['agentid']]['cp_suiteid'])}
						<p><button type="button" class="btn btn-default" role="button" disabled="disabled">已绑定</button></p>
		{else}
						<p><a href="{$bind_url_base}{$agent['agentid']}" class="btn btn-primary" role="button">绑定该应用</a></p>
		{/if}
					</div>
				</div>
			</div>
	{/foreach}
		</div>
	</div>
{/if}

{include file="$tpl_dir_base/footer.tpl"}