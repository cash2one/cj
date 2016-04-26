{include file='frontend/header.tpl'}

<body id="wbg_qj_stat1">

<h1>请假记录</h1>
{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>没有您需要查看的审批列表</span></em>
{else}
<ol class="mod_common_list">
    {include file='frontend/askoff/list_li.tpl'}
</ol>
{/if}

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}