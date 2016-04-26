{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_page = {$page};
</script>
{foreach $list as $v}
<li>
	<a class="m_link" href="/frontend/inspect/view/ins_id/{$v['ins_id']}">
		<i class="top3">{$v['_rank_id']}</i><label>{$shops[$v['csp_id']]['csp_name']}</label><em>{$v['isr_score']}{if 0 < $inspect_set['score_rule_diy']}%{else}分{/if}</em>
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}
