
{include file="$tpl_dir_base/header.tpl"}

<link href="/admincp/static/stylesheets/campaigns.css" rel="stylesheet" type="text/css" />
<div id="campaigns_add" ui-view class="stat-panel"></div>

<script type="text/javascript" src="/misc/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/misc/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="/misc/ueditor/lang/zh-cn/zh-cn.js" charset="utf-8"></script>

<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/pagination/pagination.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/submit/repeat-submit.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/cycp/upload.js"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/cycpupload/cycp-upload.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/campaigns/campaignsAddModule.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/campaigns/campaignsAddCtrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/campaigns-api.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/campaigns/campaignsPlugins.js" ></script>

<script>
    PolerEmbed.init($('#campaigns_add'), ['app.modules.campaignsAdd']);
</script>

{include file="$tpl_dir_base/footer.tpl"}
