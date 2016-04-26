{include file="$tpl_dir_base/header.tpl"}

<link href="/admincp/static/stylesheets/score.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/score.css" rel="stylesheet" type="text/css"/>

<div id="score_setup_module" ui-view></div>

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
<script type="text/javascript" src="/admincp/static/javascripts/cycp/upload_more.js"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/cycpupload/cycp-upload-more.js"></script>


<script type="text/javascript" src="/misc/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/misc/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="/misc/ueditor/lang/zh-cn/zh-cn.js" charset="utf-8"></script>



<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/score-api.js" ></script>
<!-- integral api -->
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/integral-api.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/score/setupModule.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/setupCtrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/newPrizeCtrl.js" ></script> 
<script type="text/javascript" src="/admincp/static/ng-modules/score/matchListCtrl.js" ></script> 
<!-- 积分规则页面控制器 -->
<script type="text/javascript" src="/admincp/static/ng-modules/score/integrationRule-ctrl.js" ></script>

<script>
    PolerEmbed.init($('#score_setup_module'), ['app.modules.setUp','ng.poler.plugins.pagination']);
</script>

{include file="$tpl_dir_base/footer.tpl"}