{include file="$tpl_dir_base/header.tpl"}

<div class="tab-content font12">

{foreach $list as $cmg_id => $data}

   
<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">
            {if $data['group']['cmg_icon']}<i class="fa {$data['group']['cmg_icon']} font14"></i> {/if}<strong>{$data['group']['cmg_name']|escape}</strong>
            <span class="space"></span><span class="space"></span>
            <strong>
      {if $auth_status}
            您已经授权！<a href="{$authurl}">修改授权</a>
      {else}
      <a href="{$authurl}">授权</a>
      {/if}
            </strong>
        </div>
    </div>
    <table class="table table-striped table-bordered table-hover font12">
    <colgroup>
        <col class="t-col-5" />
        <col class="t-col-12" />
        <col />
<!-- {if $type == 'used'}
        <col class="t-col-12" />
        <col class="t-col-26" />
{/if} -->
        <col class="t-col-20" />
    </colgroup>
    <thead>
        <tr>
            <th>图标</th>
            <th>应用名</th>
            <th>应用描述</th>
<!-- {if $type == 'used'}
            <th>{$lang_agentid}</th>
            <th>{$lang_app_url}</th>
{/if} -->
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        {foreach $data['list'] as $pluginid => $plugin}  
        <tr>
            <td><img src="{$plugin['_icon']}" width="40" /></td>
            <td>{$plugin['cp_name']|escape}</td>
            <td class="text-left">{$plugin['cp_description']|escape}</td>
<!-- {if $type == 'used'}
            <td>{$plugin['cp_agentid']}</td>
            <td class="text-left">{$plugin_callback_url_base}{$plugin['cp_pluginid']}</td>
{/if} -->
            <td >
            <div class="btn-group">
        <!-- 删除 -->
        {if $plugin['cp_available'] == $availables['open'] || $plugin['cp_available'] == $availables['close']}
            {$base->linkShow($delete_url_base, $pluginid, '删除', 'fa-trash-o', 'class="text-danger btn-sm _app_delete"')}
        {elseif $type != 'waited'}
            {$base->linkShow('', '', '删除', 'fa-trash-o', ' role="button" class="text-danger btn-sm"')}
        {/if}
        <!-- 启用 or 关闭 -->
        {if $plugin['cp_available'] == $availables['open']}
            {$base->linkShow($close_url_base, $pluginid, '关闭', 'fa-lock', ' role="button" class="text-warning btn-sm _app_close"')}
        {elseif $plugin['cp_available'] == $availables['close'] || $plugin['cp_available'] == $availables['new'] || $plugin['cp_available'] == $availables['delete']}
<!--            {$base->linkShow($open_url_base, $pluginid, '开启', 'fa-unlock', ' role="button" class="text-success btn-sm"')} -->
{$base->linkShow($bind_url_base, $pluginid, '绑定', 'fa-unlock', ' role="button" class="text-success btn-sm"')}
        {elseif $plugin['cp_available'] == $availables['wait_close'] || $plugin['cp_available'] == $availables['wait_open'] || $plugin['cp_available'] == $availables['wait_delete']}
            {$base->linkShow($cancel_url_base, $pluginid, $cancel_languages[$plugin['cp_available']], 'fa-clock-o', ' role="button" class="btn btn-default btn-sm"')}
        {/if}
                            </div>
            </td>
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
{if $type == 'used' && count($list) > 0}
<div class="panel panel-info">
    <div class="panel-heading"><span class="fa fa-info-circle"></span>&nbsp;<strong>应用的 {$lang_token} 与 {$lang_aeskey}</strong></div>
    <div class="panel-body">
        <ul>
            <li><strong>{$lang_token}:</strong> {$setting['token']}</li>
            <li><strong>{$lang_aeskey}:</strong> {$setting['aes_key']}</li>
        </ul>
        <span class="help-block">如微信企业平台应用与以上凭据不一致请修改微信企业平台应用。</span>
    </div>
</div>
{/if}
{foreachelse}
	<div class="alert alert-warning">
	{$data_none_msg}
	</div>
{/foreach}
</div>

<script type="text/javascript">
jQuery(function(){
	/*
	jQuery('._app_delete').click(function(){
		var url = this.href;
		var options = {
			"title":"<span style=\"color:blue\">应用删除警告</span>",
			"content":"<strong style=\"color:red;font-size:30px\"><i class=\"fa fa-warning\"></i> 警告！</strong>删除应用后微信平台将不再显示该应用，您的企业将无法使用该应用的相关功能，该应用的数据也将会删除，请慎重进行此操作。",
			"confirm_cbk":function(){
				window.location.href=url;
				return true;
			}
		};
		WG.popup(options);
		return false;
	});
	
	jQuery('._app_close').click(function(){
		var url = this.href;
		var options = {
			"title":"应用关闭警告",
			"content":"<strong style=\"color:blue;font-size:30px\"><i class=\"fa fa-info-circle\"></i> 警告！</strong> 关闭应用，微信平台将不再显示该应用，您的企业将无法使用该应用相关功能，但该应用的数据将会保留，请慎重进行此操作。",
			"confirm_cbk":function(){
				window.location.href=url;
				return true;
			}
		};
		WG.popup(options);
		return false;
	});
	*/
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
