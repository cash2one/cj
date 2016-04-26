{include file="$tpl_dir_base/header.tpl"}
<link href="/admincp/static/stylesheets/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]>
<link rel="stylesheet" href="/admincp/static/stylesheets/font-awesome-ie7.min.css">
<![endif]-->
<link href="/admincp/static/stylesheets/titatoggle-dist-min.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/sign.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />

<div id="sign-class-main-module" ui-view class="stat-panel"></div>

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
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.min.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.zh-CN.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/dateplugin/datetimepicker.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/module-class-main.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-class-main-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-class-add-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-class-edit-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/input-focus-directive.js" ></script>

<script>
	PolerEmbed.init($('#sign-class-main-module'), ['app.modules.sign.class.main','ng.poler.plugins.pagination']);
</script>


{include file="$tpl_dir_base/footer.tpl"}