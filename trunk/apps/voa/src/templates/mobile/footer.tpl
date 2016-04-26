</div>
{* 确定是否使用图片画廊功能，如需要使用引入footer模板时，加入内部变量：SHOWIMG=1 *}
{* 并在需要触发画廊的图片外部容器定义一个class=_show_gallery *}
{if !empty($SHOWIMG) && (empty($_cyoa_jsapi_) || !in_array('previewImage', $_cyoa_jsapi_))}
	{$_cyoa_jsapi_[] = 'previewImage'}
{/if}

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

{* 加载图片画廊绑定 *}
{if !empty($SHOWIMG)}
require(["zepto", "frozen", "jweixin", "showimg"], function ($, fz, wx, showimg) {
	// 绑定图片点击触发动作
	$('#cyoa-body').on('click', '._show_gallery img', function () {
		var s = new showimg();
		s.show($(this));
	});
});
{/if}
</script>
{/if}

{if !empty($_cyoa_h5mod_)}
{* 加载指定的模块js文件 *}
	{foreach $_cyoa_h5mod_ as $_jsfile}
<script type="text/javascript" src="{$wbs_javascript_path}/h5mod/{$_jsfile}?_t={if 'test.vchangyi.com' == $domain || 'local.vchangyi.net' == $domain}{$timestamp}{else}{rgmdate($timestamp,'Ymd')}{/if}"></script>
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

<script type="text/javascript">
{if !empty($_cyoa_js_code_)}
{* 涉及H5组件注入的js代码 *}
	{foreach $_cyoa_js_code_ as $_code}
{$_code}
	{/foreach}
{/if}

</script>

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
{if $stat_plugin_id}
<script type="text/javascript">
var stat_plugin_id = '{$stat_plugin_id}';
var stat_referer = '{$stat_referer}';
{literal}
require(["zepto"], function($) {
	$.post('/frontend/stat/log', { plugin : stat_plugin_id, referer : stat_referer});
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
