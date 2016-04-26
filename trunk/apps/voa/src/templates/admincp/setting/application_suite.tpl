{include file="$tpl_dir_base/header.tpl" css_file='install.css'}
{if !$installed}
	{* 从未安装过任何应用 *}
	<div class="bind-tip">
		<strong>如何绑定微信企业号？</strong>
		<span class="bind-tip-btn"><i class="fa fa-angle-double-up"></i></span>
	</div>
	<div class="bind-step"{if $install_app} style="display: none"{/if}>
		<h2>仅需三步，快速绑定</h2>
		<div class="bind-step-intro">
			<div class="intro-step step-1">
				<span class="line-1">首先你需要拥有一个企业号。若没有，点击下方快速注册</span>
				<span class="line-2"><a href="https://mp.weixin.qq.com/cgi-bin/readtemplate?t=register/step1_tmpl&lang=zh_CN" class="btn btn-info" target="_blank">注册企业号</a></span>
				<span class="line-3"><a href="http://www.vchangyi.com/bbs/thread-9-1-1.html" class="faq-link" target="_blank">如何注册企业号</a></span>
			</div>
			<div class="intro-step step-2">
				<span class="line-1">点击进入「应用中心」，选择需要安装的应用或套件，进行授权安装</span>
				<span class="line-2"><a href="/admincp/setting/application/list/?install_app=1" class="btn btn-info" target="_blank">安装套件/应用</a></span>
				<span class="line-3"><a href="http://www.vchangyi.com/bbs/thread-269-1-1.html" class="faq-link" target="_blank">如何安装应用</a></span>
			</div>
			<div class="intro-step step-3">
				<span class="line-1">进入「人员管理」，录入人员信息并邀请关注，完成后即可使用</span>
				<span class="line-2"><a href="/admincp/manage/" class="btn btn-primary" target="_blank">进入人员管理</a></span>
				<span class="line-3"><a href="http://www.vchangyi.com/bbs/thread-257-1-1.html" class="faq-link" target="_blank">如何录入通讯录</a></span>
			</div>
			<div class="clearfix"></div>
		</div>
		<hr />
		<div class="view-step-doc">
			<a href="http://www.vchangyi.com/bbs/" target="_blank"><span>查看更多</span></a>
		</div>
	</div>
{else}
	{* 已安装过 *}
	{if $is_suite_auth_site}
	<div class="alert alert-warning" role="alert" style="background-color: #EDF1FC;border-color: #ff0000;color: #ac2925;">
		<strong style="font-size: large;color: #ac2925">关于微信企业号应用套件授权相关信息，<a href="http://www.vchangyi.com/faq/206.html" target="_blank">可点击这里了解</a></strong>。
	</div>
	{else}
<div class="alert alert-danger" role="alert">
	<strong style="font-size:15px">应用套件授权及应用绑定服务目前正在内部测试中，暂时不支持应用的开关以及绑定操作，稍后开放敬请期待。</strong>
	<br />
	关于微信企业号应用套件授权相关信息，<a href="http://www.vchangyi.com/faq/206.html" target="_blank">可点击这里了解</a>。
</div>
	{/if}
{/if}
<div class="tab-content font12" id="app-list">

{foreach $plugin_list as $cpg_id => $data}
	<div class="table-light">
		<div class="table-header" style="background-color:#d9edf7;margin:0;padding:0;">
			<div class="table-caption" style="height:46px;line-height:46px;text-indent:10px;font-size:18px;">

				<span class="pull-right">
{if $is_suite_auth_site}
					<a href="{$data['group']['_authurl']}" title="批量安装" target="_blank" class="btn btn-danger">批量安装 <strong>{$data['group']['cpg_name']|escape}</strong> 套件应用</a>
{/if}
				</span>

	{if $data['group']['cpg_icon']}
				<!-- <i class="fa {$data['group']['cpg_icon']} font14"></i> -->
	{/if}
				<strong>{$data['group']['cpg_name']|escape}</strong>
			</div>
		</div>
		<table class="table table-striped table-bordered table-hover font12">
			<colgroup>
				<col class="t-col-5" />
				<col class="t-col-15" />
				<col />
				<col class="t-col-20" />
			</colgroup>
			<thead>
				<tr>
					<th>图标</th>
					<th>应用名</th>
					<th>应用描述</th>
					<th>绑定微信企业号应用</th>
				</tr>
			</thead>
			<tbody>
	{foreach $data['list'] as $pluginid => $plugin}
				<tr>
					<td><img src="{$plugin['_icon']}" width="40" /></td>
					<td>{$plugin['cp_name']|escape}</td>
					<td class="text-left">{$plugin['cp_description']|escape}</td>
		{if $plugin['_is_bind']}
					<td>
						<span><i class="fa fa-lock"></i> 已安装</span>
						<br />
						<a href="https://qy.weixin.qq.com/" target="_blank" class="text-default" title="点击进入微信企业号平台">可在微信企业号后台解除绑定</a>
					</td>
		{else}
					<td>
						{$base->linkShow($plugin['_bindurl'], '', '安装', 'fa-unlock', ' role="button" class="text-success btn-sm"')}
					</td>
		{/if}
				</tr>
	{foreachelse}
				<tr>
					<td colspan="4" class="warning">
						本类型暂无可用的应用
					</td>
				</tr>
	{/foreach}
			</tbody>
		</table>
	</div>
{foreachelse}
	<div class="alert alert-danger">暂无可用的应用信息</div>
{/foreach}
</div>

{if !empty($install_list)}
	{if count($install_list) > 1}
		{include file="$tpl_dir_base/application_install_multi" install_list=$install_list}
	{else}
		{include file="$tpl_dir_base/application_install_single" install_list=$install_list}
	{/if}
{/if}

<script type="text/javascript">
var install_app = {if $install_app}true{else}false{/if};
jQuery(function () {
	jQuery('.bind-tip').click(function () {
		var jq_step = jQuery('.bind-step');
		var hidden = jq_step.is(':hidden');
		var app_list = jQuery('#app-list');
		if (hidden) {
			jq_step.show();
			if (!install_app) {
				//app_list.hide();
				$(this).find('.fa').addClass('fa-angle-double-up').removeClass('fa-angle-double-down');
			}
		} else {
			jq_step.hide();
			if (!install_app) {
				//app_list.show();
				$(this).find('.fa').addClass('fa-angle-double-down').removeClass('fa-angle-double-up');
			}
		}
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}