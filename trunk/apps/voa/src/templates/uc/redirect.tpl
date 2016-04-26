{include file='frontend/header.tpl'}

<script type="text/javascript">
function referURL(url) {
	var isIe = (document.all) ? true : false;
	if (isIe) {
		var linka = document.createElement('a');
		linka.href = url;
		document.body.appendChild(linka);
		linka.click();
	} else window.location = url;
}

// 为了兼容 ie8 可能出现的 refer 值丢失的bug
window.onload = function() {
	referURL("{$url}");
};
</script>

{include file='frontend/footer.tpl'}