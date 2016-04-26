{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}
<div class="panel panel-default">
	<div class="panel-body">
		<div class="exam-view">
				<div class="i-tt">{$paper.name}</div>
				<p>{$paper.intro}</p>
				{if $tis}
					{foreach $tis as $_k => $_v}
				<div class="i-question-tt">({$types[$_v['type']]}) {$_v.title} ({$_v['score']}分)
					{if $myanswers[$_v['id']]['my_answer']}
						{if $myanswers[$_v['id']]['is_pass']==0}
							<i class="fa fa-exam-wrong"> 回答错误</i>
						{else}
							<i class="fa fa-exam-right"> 回答正确</i>
						{/if}
					{else}
						<i class="fa fa-exam-no"> 未回答</i>
					{/if}
				</div>

						{if $_v['type']==0||$_v['type']==3}
				<ul class="i-options">
							{foreach $_v['options'] as $kk=>$_q}
					<li>{chr($kk+65)}、{$_q}</li>
							{/foreach}
				</ul>
						{/if}
				<div class="i-my-answer">
					您的答案：{str_replace("\r\n","",$myanswers[$_v['id']]['my_answer'])}
				</div>
				<div class="i-answer">答案：{str_replace("\r\n","",$_v['answer'])}</div>
					{/foreach}
				{/if}

		</div>

	</div>
	
</div>


<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		<div class="row">
			<div class="col-md-4"><a href="{$tjdetail_url}" class="btn btn-default col-md-9">返回</a></div>
		</div>
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}
