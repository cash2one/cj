{include file='frontend/header.tpl'}

<body id="wbg_bx_list">

<h1>{if 'doing' == $status}待我{else}已{/if}审批列表</h1>
{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>没有您需要查看的审批列表</span></em>
{else}
<ul class="mod_common_list">
    {include file='frontend/askoff/deal_li.tpl'}
</ul>
{/if}

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}