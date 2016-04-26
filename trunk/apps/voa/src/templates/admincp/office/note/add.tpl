{include file="$tpl_dir_base/header.tpl"}
<link href="/admincp/static/stylesheets/note.css" rel="stylesheet" type="text/css" />

<div id="note-add-module" ui-view class=""></div>

<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-ui-router.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/angular-route.min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/underscore-min.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/lib/ng.poler.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/ng.poler.embed/ng.poler.embed.utils.js" ></script>


<script type="text/javascript" src="/misc/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/misc/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="/misc/ueditor/lang/zh-cn/zh-cn.js" charset="utf-8"></script>


<script type="text/javascript" src="/admincp/static/ng-modules/app.bootstrap.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/note-api.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/note/add_Module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/note/add_ctrl.js" ></script>

<script>
    PolerEmbed.init($('#note-add-module'), ['app.modules.noteadd']);
</script>


{include file="$tpl_dir_base/footer.tpl"}