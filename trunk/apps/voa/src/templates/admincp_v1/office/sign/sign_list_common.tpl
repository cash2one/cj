<ul class="nav nav-tabs">
	<li {if $searchBy['sr_type']=='-1'}class="active"{/if}><a href="{$link_all}">全部</a></li>
	<li {if $searchBy['sr_type']==1}class="active"{/if}><a href="{$link_work_on}">上班</a></li>
	<li {if $searchBy['sr_type']==2}class="active"{/if}><a href="{$link_work_off}">下班</a></li>
	<li {if $searchBy['sr_type']==3}class="active"{/if}><a href="{$link_upposition}">上报地理位置</a></li>
</ul>
<br />
