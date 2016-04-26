{include file="$tpl_dir_base/header.tpl"}
<link href="/admincp/static/stylesheets/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]>
<link rel="stylesheet" href="/admincp/static/stylesheets/font-awesome-ie7.min.css">
<![endif]-->
<link href="/admincp/static/stylesheets/titatoggle-dist-min.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/sign.css" rel="stylesheet" type="text/css" />
<div id="sign-settings-module" ui-view class="stat-panel"></div>

<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/jquery-ui.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/pagination/pagination.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/dateplugin/date-picker.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/submit/repeat-submit.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/sgin-api.js" ></script>

<!-- 考勤 -->
<script type="text/javascript" src="/admincp/static/ng-modules/sign/module-settings.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-settings-main-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-settings-signin-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-settings-signout-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-settings-weixin-ctrl.js" ></script>


<script>
    PolerEmbed.init($('#sign-settings-module'), ['app.modules.sign.settings','ng.poler.plugins.pagination']);
</script>

{include file="$tpl_dir_base/footer.tpl"}