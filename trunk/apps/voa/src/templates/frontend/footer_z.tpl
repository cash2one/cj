{if $qywx_send}
<script type="text/javascript">
{literal}
require(["zepto"], function($) {
	$.get({'url': '/qywxmsg/send'});
});
{/literal}
</script>
{/if}
</body>
</html>
