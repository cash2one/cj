{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>试卷预览</strong></h3>
	</div>
	<div class="panel-body">
		<div class="exam-view">
			{if $paper.type!=2}
				<div class="i-tt">{$paper.name}</div>
				{if $tis}
					{foreach $tis as $_k => $_v}
				<div class="i-question-tt">({$types[$_v['type']]}) {$_v.title} ({$_v['score']}分)<span>答案：{str_replace("\r\n","",$_v['answer'])}</span></div>

						{if $_v['type']==0||$_v['type']==3}
				<ul class="i-options">
							{foreach $_v['options'] as $kk=>$_q}
					<li>{chr($kk+65)}、{$_q}</li>
							{/foreach}
				</ul>
						{/if}
					{/foreach}
				{/if}
			{else}
				<p class="i-tt text-danger">考试题目在前台随机生成，无法查看</p>
			{/if}

		</div>

	</div>
	
</div>
{include file="$tpl_dir_base/footer.tpl"}
