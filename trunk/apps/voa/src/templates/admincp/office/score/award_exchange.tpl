{include file="$tpl_dir_base/header.tpl"}

<link href="/admincp/static/stylesheets/award_exchange.css" rel="stylesheet" type="text/css"/>
<link href="/admincp/static/stylesheets/score.css" rel="stylesheet" type="text/css"/>

<div id="score_award_exchange_module" ui-view></div>

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
<script type="text/javascript" src="/admincp/static/ng-modules/app.config.js" ></script>

<script type="text/javascript" src="/admincp/static/ng-modules/api-service/module.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/api-service/score-api.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/awardExchangeModule.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/exchangeApplicationCtrl.js" ></script>
<script type="text/javascript" src="/admincp/static/ng-modules/score/awardListCtrl.js" ></script>

<script>
    PolerEmbed.init($('#score_award_exchange_module'), ['app.modules.awardExchange','ng.poler.plugins.pagination']);
</script>

{include file="$tpl_dir_base/footer.tpl"}