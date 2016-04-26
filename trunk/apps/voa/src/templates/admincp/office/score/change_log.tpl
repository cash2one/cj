{include file="$tpl_dir_base/header.tpl"}

<div id="score_change_log_module" ui-view></div>

<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/pagination/pagination.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/dateplugin/date-picker.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/submit/repeat-submit.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.min.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/score-api.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/changeLogModule.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/changeLogCtrl.js" ></script>

<script>
    PolerEmbed.init($('#score_change_log_module'), ['app.modules.changeLog','ng.poler.plugins.pagination']);
</script>

{include file="$tpl_dir_base/footer.tpl"}