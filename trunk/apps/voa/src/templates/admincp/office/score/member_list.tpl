{include file="$tpl_dir_base/header.tpl"}

<link href="/admincp/static/stylesheets/contacts.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/score.css" rel="stylesheet" type="text/css"/>

<div id="score_member_list_module" ui-view></div>

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
<!-- memberlist api -->
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/integral-api.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/menberListModule.js" ></script>

<!-- memberlist页面控制器 -->
<script type="text/javascript" src="/admincp/static/ng-modules/score/memberList-ctrl.js" ></script>
<!-- <script type="text/javascript" src="/admincp/static/ng-modules/integral/module.js" ></script> -->
<!-- memberDetails页面控制器 -->
<script type="text/javascript" src="/admincp/static/ng-modules/score/memberDetails-ctrl.js" ></script>
<script>
    PolerEmbed.init($('#score_member_list_module'), ['app.modules.memberList','ng.poler.plugins.pagination']);
</script>

{include file="$tpl_dir_base/footer.tpl"}