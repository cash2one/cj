	</div>
	<div id="main-menu-bg"></div>
</div>

{if !empty($_cycp_js_)}
{* 加载指定的模块js文件 *}
	{foreach $_cycp_js_ as $_jsfile}
<script type="text/javascript" src="{$JSDIR}/cycp/{$_jsfile}?_t={if 'test.vchangyi.com' == $domain}{$timestamp}{else}{rgmdate($timestamp,'Ym')}{/if}"></script>
	{/foreach}
{/if}
<script type="text/javascript">
	//init.push(function () {
	//})
	window.PixelAdmin.start(init);
</script>
<script type="text/javascript">
$.ajax('/api/common/post/sendmsg');
</script>
</body>
</html>