{include file="$tpl_dir_base/header.tpl"}
<link href="/admincp/static/stylesheets/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]>
<link rel="stylesheet" href="/admincp/static/stylesheets/font-awesome-ie7.min.css">
<![endif]-->
<link href="/admincp/static/stylesheets/titatoggle-dist-min.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/sign.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />

<div id="sign-member-main-module" ui-view class="stat-panel"></div>

<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=ZgwqnK2tl1y2k6oRI8DCyZ2kjmOcsz22"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/jquery-ui.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/pagination/pagination.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/map/baidu-map.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/submit/repeat-submit.js" ></script>



<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/sgin-api.js" ></script>

<!-- 考勤 -->
<script type="text/javascript" src="/admincp/static/ng-modules/sign/module-member-main.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-member-add-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-member-edit-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-member-main-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/sign/sign-member-selectclass-dialog-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/dateplugin/datetimepicker.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.min.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.zh-CN.js"></script>


<script>
    PolerEmbed.init($('#sign-member-main-module'), ['app.modules.sign.member.main','ng.poler.plugins.pagination']);
</script>


{include file="$tpl_dir_base/footer.tpl"}