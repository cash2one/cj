{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="push_message" class="col-sm-2 control-label">推进消息：</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="push_message" name="message" rows="8"></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">选择成员：</label>
		<div class="col-sm-10">
{foreach $users AS $_data}
			<label class="checkbox-inline">
				<input type="checkbox" name="project_uids[{$_data['m_uid']}]" value="{$_data['m_uid']}" /> {$_data['m_username']|escape}
			</label>
{/foreach}
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">立即推送</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}