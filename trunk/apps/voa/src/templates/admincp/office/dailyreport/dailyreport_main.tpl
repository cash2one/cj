{include file="$tpl_dir_base/header.tpl"}
<link href="/admincp/static/stylesheets/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/dailyreport.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/ng-modules/ng.input.assembly/ng.input.assembly.css" rel="stylesheet" type="text/css" />

<div id="dailyreport-module" ui-view class=""></div>

<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/jquery-ui.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.input.assembly/ng.input.assembly.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/pagination/pagination.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/suidatepicker.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/dateplugin/date-picker.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/submit/repeat-submit.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.min.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/dailyreport-api.js" ></script>
<!-- <script type="text/javascript" src="/admincp/static/ng-modules/api-service/member-api.js" ></script> -->

<script type="text/javascript" src="/admincp/static/ng-modules/dailyreport/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/dailyreport/report-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/dailyreport/report-view-ctrl.js" ></script>
<!-- <script type="text/javascript" src="/admincp/static/ng-modules/member/instance-service.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/member/member-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/member/member-props-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/member/member-tag-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/member/add-props-dialog-ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/member/tab-directive.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/member/warn-dialog-service.js" ></script> -->

<script>
    PolerEmbed.init($('#dailyreport-module'), ['app.modules.dailyreport','ng.poler.plugins.pagination']);
</script>


{include file="$tpl_dir_base/footer.tpl"}