{include file="$tpl_dir_base/header.tpl"}
<script>
window._appname = "superreport";
window._staticurl = "{$staticUrl}";
window._jsdir = "{$JSDIR}";

</script>
<link rel="stylesheet" href="{$JSDIR}customized/app/superreport/css/main.css" />
<link href="{$JSDIR}customized/lib/jquery-datetimepicker/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />
<link href="{$JSDIR}customized/3rdparty/jquery-bootgrid/jquery.bootgrid.css" rel="stylesheet" type="text/css" />
<div id="container_main">
</div>
<script src="{$JSDIR}customized/lib/requirejs/require.js" data-main="{$staticUrl}javascripts/customized/main.js"></script>
{include file="$tpl_dir_base/footer.tpl"}
