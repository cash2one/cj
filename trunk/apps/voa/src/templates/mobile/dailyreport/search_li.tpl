<section class="ui-selector ui-selector-line">
	<div class="ui-selector-content">
		<ul >
			<li class=" ui-selector-item ui-border-b">
				<h3><p id="option_p">{if empty($sotext)}全部{else}{$sotext}{/if}</p></h3>
				<select id="daily_type" name="daily_type" data-lis-oldselidx="15" onchange="change()">
					<option value="-1">全部</option>
{foreach $dailyType as $k => $v}
					{if $v[1] == 1}<option value="{$v[0]}"{if $sotext == $v[0]} selected{/if}>{$v[0]|escape}</option>{/if}
{/foreach}
				</select>
			</li>
		</ul>
	</div>
</section>

{if empty($list)}
<div class="ui-tab">
	<ul class="ui-tab-content" style="width:300%">
		<li>
			<section class="ui-notice ui-notice-norecord"> <i></i>
				<p>暂无审批数据</p>
			</section>
		</li>
		<li>r</li>
		<li></li>
	</ul>
</div>
{else}
<div class="frozen-module-dom">
	<div class="ui-form ">
		<input type="hidden" id="ac" value="{$ac}" />
		{foreach $list as $v name=search_li}
		<div class="ui-form-item ui-form-item-order ui-form-item-link {if !$smarty.foreach.search_li.last} ui-border-b{/if}">
			<a href="/dailyreport/view/{$v.dr_id}">
			 {if 'mine' != $ac}{$v['m_username']|escape}&nbsp;{/if}
			 {if 'mine' != $ac}{/if}{$v['_reporttime_fmt']['m']}-{$v['_reporttime_fmt']['d']} {$weeknames[$v['_reporttime_fmt']['w']]}日报{if 'mine' != $ac}{/if}
			</a>
		</div>
		{/foreach}
	</div>
</div>
{/if}


<script type="text/javascript">
{literal}
require(["zepto", "frozen"], function($, fz) {
	$(document).ready(function() {
		function change(){
			var op_val;
			$('#daily_type option').each(function(index, opt) {
				if ($(opt).prop("selected")) {
					op_val = $(opt).text();
					$('#option_p').text(op_val);
				}
			});
			window.location.href = '/dailyreport/so?sotext=' + op_val+'&ac='+$('#ac').val();
		}
	});
});
{/literal}
</script>