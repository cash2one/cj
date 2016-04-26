{include file="$tpl_dir_base/header.tpl"}
<style>
.form-small {
	width: 100px!important;
}
.col-sm-4 {
	width: 26%!important;
}
.col-sm-8 {
	width: 74%!important;
}
</style>
<script>
window._appname = "travel";
window._staticurl = "{$staticUrl}";
window._jsdir = "{$JSDIR}";
window._plusinid = "{$pluginid}";
window._style = "{$style}";
</script>
<link rel="stylesheet" href="{$JSDIR}customized/app/travel/css/main.css" />
<link href="{$JSDIR}customized/lib/jquery-datetimepicker/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />
<link href="{$JSDIR}customized/3rdparty/jquery-bootgrid/jquery.bootgrid.css" rel="stylesheet" type="text/css" />
<div id="container_main">
</div>
<script src="{$JSDIR}customized/lib/requirejs/require.js" data-main="{$staticUrl}javascripts/customized/main.js"></script>
{include file="$tpl_dir_base/footer.tpl"}
