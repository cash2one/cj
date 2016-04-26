<div class="mod_top_search">
	<div class="sinner">
		<!--placeholder请酌情修改-->
		<input type="text" value="{$iptvalue}" placeholder="{$placeholder}" />
	</div>
</div>
{literal}
<script>
$onload(function() {
	var $tc = $one('.mod_top_search'),
		$ipt = $one('input', $tc);
	$ipt.addEventListener('keyup', function(e) {
		if (e.which === 13) { //回车
			var cur_url = window.location.href;
			var reg = new RegExp("\\??sotext=(.*?)$", 'ig');
			cur_url = cur_url.replace(reg, '');
			cur_url += (-1 == cur_url.indexOf('?') ? '?' : '&') + 'sotext=' + $ipt.value;
			window.location.href = cur_url;
		};
	});
});
</script>
{/literal}
