{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label class="col-sm-2 control-label">发起人</label>
		<div class="col-sm-10">
			<p class="form-control-static"><strong>{$project['m_username']|escape}</strong> &nbsp; 更新自 {$project['_updated']}</p>
		</div>
	</div>
	<div class="form-group">
		<label for="p_subject" class="col-sm-2 control-label">名称</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="p_subject" name="p_subject" placeholder="名称" value="{$project['p_subject']|escape}" maxlength="250" />
		</div>
	</div>
	<div class="form-group">
		<label for="p_begintime" class="col-sm-2 control-label">开始时间</label>
		<div class="col-sm-10">
			<input type="date" class="form-control" id="p_begintime" name="p_begintime" value="{$project['_begintime']|escape}" />
		</div>
	</div>
	<div class="form-group">
		<label for="p_endtime" class="col-sm-2 control-label">结束时间</label>
		<div class="col-sm-10">
			<input type="date" class="form-control" id="p_endtime" name="p_endtime" value="{$project['_endtime']|escape}" />
		</div>
	</div>
	<div class="form-group">
		<label for="p_status_{$project['_open']}" class="col-sm-2 control-label">关闭</label>
		<div class="col-sm-10">
{foreach $base->_project_open as $_id => $_name}
			<label class="radio-inline"><input type="radio" id="p_status_{$_id}" name="p_status" value="{$_id}"{if $project['_open']==$_id} checked="checked"{/if} /> {$_name}</label>
{/foreach}
		</div>
	</div>
	<div class="form-group">
		<label for="p_message" class="col-sm-2 control-label">具体任务备注</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="p_message" name="p_message" rows="5">{$project['p_message']|escape}</textarea>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">保存</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}