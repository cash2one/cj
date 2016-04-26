{if $qywx_send}
<script>
{literal}
$ajax('/qywxmsg/send', 'POST', //[ajax] url & method
	{}, function(ajaxResult) {}, //[ajax] callback
	true //[ajax] use json
);
{/literal}
</script>
{/if}

{if $stat_plugin_id}
	<script type="text/javascript">
		var stat_plugin_id = '{$stat_plugin_id}';
		var stat_referer = '{$stat_referer}';
		{literal}
		require(["zepto"], function($) {
			$ajax('/frontend/stat/log', 'POST', { plugin : stat_plugin_id, referer : stat_referer},function(result){});
		});
		{/literal}
	</script>
{/if}
<!-- 2014年度专题链接变量名：year2014_url，以下可以写入在全站范围内显示的入口给用户 -->
{if $year2014_url}
<div id="float2014" style="position:fixed;top:20%;right:6px;"><a href="{$year2014_url}"><img style="width:60px;height:60px;" src="/static/images/year2014/year2014.png"/></a></div>
{/if}
</body>
</html>