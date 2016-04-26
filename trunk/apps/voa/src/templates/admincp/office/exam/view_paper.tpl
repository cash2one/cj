{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}


<ul class="nav nav-tabs font12">
	<li{if $partin==0} class="active"{/if}>
		<a href="#papaer_base" data-toggle="tab">
			基本信息&nbsp;
		</a>
	</li>
	<li{if $partin==1} class="active"{/if}>
		<a href="#papaer_questions" data-toggle="tab">
			试卷题目&nbsp;
		</a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="papaer_base">
	
		<div class="panel panel-default">
			<div class="panel-body exam-paper-view">
				
				<div class="form-group">
					<label class="control-label col-sm-2">试卷名称:</label>
					<div class="col-sm-9">{$paper['name']}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试范围:</label>
					<div class="col-sm-9">
						{if $paper['is_all']}
							全公司
						{else}
							{if $departments}<pre>{$departments}</pre>{/if}
							{if $members}<pre>{$members}</pre>{/if}
						{/if}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">封面图片:</label>
					<div class="col-sm-9">
						<img src="{$paper['picurl']}" width="200">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试时间:</label>
					<div class="col-sm-9">
						{if !empty($paper['begin_time'])}
							{rgmdate($paper['begin_time'], 'Y/m/d H:i')} -
							{rgmdate($paper['end_time'], 'Y/m/d H:i')}
						{else}
						-
						{/if}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试状态:</label>
					<div class="col-sm-9">
						{$paper['status_show']}
						{if $paper['status_show']=='已终止'}
						<table class="exam-tb">
							<tr>
								<th width="90">操作人员:</th>
								<td>{$paper['reason_user']}</td>
							</tr>
							<tr>
								<th>操作时间:</th>
								<td>{rgmdate($paper['reason_time'], 'Y/m/d H:i')}</td>
							</tr>
							<tr>
								<th>操作原因:</th>
								<td>{$paper['reason']}</td>
							</tr>
						</table>
						{/if}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试时长:</label>
					<div class="col-sm-9">{$paper['paper_time']} 分钟</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试提醒:</label>
					<div class="col-sm-9">
						{if $paper['is_notify']}
							<p>考试开始前 {$paper['notify_begin']} 分钟提醒</p>
							<p>考试结束前 {$paper['notify_end']} 分钟提醒</p>
						{else}
							未启用
						{/if}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试总分:</label>
					<div class="col-sm-9">{$paper['total_score']} 分</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">及格总分:</label>
					<div class="col-sm-9">{$paper['pass_score']} 分</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试说明:</label>
					<div class="col-sm-9">{$paper['intro']}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">考试情况:</label>
					<div class="col-sm-9">
						<p>
							未参与 {$no_join_count} 人 
							{if $paper['status']>=1&&time()>$paper['begin_time']}
								<a href="{$tjdetail_url}">查看考试统计</a>
							{/if}
						</p>
						<p>已参与 {$join_count} 人</p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="tab-pane" id="papaer_questions">
		
		<div class="panel panel-default">

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

	</div>
</div>

<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		<div class="row">
			<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
		</div>
	</div>
</div>


{include file="$tpl_dir_base/footer.tpl"}
