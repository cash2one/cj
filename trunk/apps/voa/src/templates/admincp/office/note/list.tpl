{include file="$tpl_dir_base/header.tpl"}
<link href="/admincp/static/stylesheets/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<link href="/admincp/static/stylesheets/note.css" rel="stylesheet" type="text/css" />
<link href="/misc/ueditor/themes/default/css/ueditor.css" rel="stylesheet" type="text/css" />


<div id="note-list-module" ui-view class=""></div>

<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/pagination/pagination.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/plugins/submit/repeat-submit.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/angular-sanitize.js" ></script>


<script type="text/javascript" src="/misc/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/misc/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="/misc/ueditor/lang/zh-cn/zh-cn.js" charset="utf-8"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/plugins/dateplugin/date-picker.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.min.js" ></script>
<script type="text/javascript" src="/admincp/static/javascripts/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/note-api.js" ></script>


<script type="text/javascript" src="/admincp/static/ng-modules/note/list_Module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/note/list_ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/note/edit_ctrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/note/view_ctrl.js" ></script>
<script>
    PolerEmbed.init($('#note-list-module'), ['app.modules.notelist','ng.poler.plugins.pagination']);
</script>


{include file="$tpl_dir_base/footer.tpl"}