</div>
{* 加载微信js接口库 *}
{if !empty($_cyoa_jsapi_)}
<script type="text/javascript">
{if !empty($jsapi_debug)}
{$jsapi_debug = 1}
{else}
{$jsapi_debug = 0}
{/if}
function wxjsapi_config(owx) {
	if (typeof(owx) != 'undefined') {
		var wx = owx;
	}
	{cyoa_jsapi debug=$jsapi_debug list=$_cyoa_jsapi_}
}
if (typeof(wx) == 'undefined' || !window.wx) {
	require(["jweixin"], function (wx) {
		wxjsapi_config(wx);
	});
} else {
	wxjsapi_config(wx);
}
</script>
{/if}

{if !empty($_cyoa_h5mod_)}
{* 加载指定的模块js文件 *}
	{foreach $_cyoa_h5mod_ as $_jsfile}
<script type="text/javascript" src="{$wbs_javascript_path}/h5mod/{$_jsfile}?_t={if 'test.vchangyi.com' == $domain}{$timestamp}{else}{rgmdate($timestamp,'Ymd')}{/if}"></script>
	{/foreach}
{/if}

{if !empty($_cyoa_jsapi_code_)}
{* 页面加载注入微信js接口相关代码片段 *}
<script type="text/javascript">
require(["jweixin"], function (wx) {
	{foreach $_cyoa_jsapi_code_ as $_code}
{$_code}
	{/foreach}
});
</script>
{/if}

{if !empty($_cyoa_userselector_)}
{* 加载选人组件控件js *}
<script type="text/javascript">
require(["zepto", "underscore", "addrbook", "frozen"], function($, _, addrbook, fz) {
	{foreach $_cyoa_userselector_ as $_js}
	{$_js}
	{/foreach}
});
</script>
{/if}

{if $qywx_send}
{* 前端触发发送微信消息 *}
<script type="text/javascript">
{literal}
require(["zepto"], function($) {
	$.get('/qywxmsg/send');
});
{/literal}
</script>
{/if}
</section>
<section class="section_container" style="display:none">
	<div id="addrbook" class="ui-tab"></div>
</section>
</body>
</html>