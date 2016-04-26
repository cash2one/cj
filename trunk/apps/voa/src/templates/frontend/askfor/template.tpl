{include file='frontend/header.tpl'}

<body id="wbg_spl_list">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>


{if empty($templates)}
<em class="mod_empty_notice"><span>请先在后台设置审批流程</span></em>
{else}
<ul class="mod_common_list" id="askfor_list">
	{foreach $templates as $v}
    <li>
    	<a href="/askfor/new/{$v.aft_id}" class="m_link">
    		<p style="margin:0;padding:0;float:left;">
    			<h1><span>{$v.name}</span></h1>
    		</p>
    	</a>
    </li>
    {/foreach}

</ul>
{/if}

{include file='frontend/footer.tpl'}
