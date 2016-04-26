{include file='admincp/header.tpl'}

<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="v_subject" class="col-sm-2 control-label">投票主题</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="v_subject" name="v_subject" placeholder="主题" value="{$vote['v_subject']|escape}" maxlength="80" />
		</div>
	</div>
	<div class="form-group">
		<label for="v_begintime" class="col-sm-2 control-label">开始时间</label>
		<div class="col-sm-10">
			<input type="date" class="form-control" id="v_begintime" name="v_begintime" value="{$vote['_begintime_input']}" />
		</div>
	</div>
	<div class="form-group">
		<label for="v_endtime" class="col-sm-2 control-label">结束时间</label>
		<div class="col-sm-10">
			<input type="date" class="form-control" id="v_endtime" name="v_endtime" value="{$vote['_endtime_input']}" />
		</div>
	</div>
	<div class="form-group">
		<label for="v_endtime" class="col-sm-2 control-label">限制参与投票人</label>
		<div class="col-sm-10">
{foreach $memberList AS $_id => $_data}
			<span class="col-sm-2"><label class="checkbox-inline"><input type="checkbox" name="uids[{$_id}]" value="{$_id}"{if isset($permit_users[$_id])} checked="checked"{/if} /> {$_data['m_username']}</label></span>
{/foreach}
		</div>
	</div>
	<div class="panel panel-default font12">
		<div class="panel-heading"><strong>投票选项</strong></div>
		<div class="panel-body">
			<div class="form-group">
				<div class="col-sm-2 text-right text-danger"><strong>删除</strong></div>
				<div class="col-sm-10"><strong>选项内容</strong></div>
			</div>
{foreach $options AS $_id => $_data}
			<div class="form-group">
				<div class="col-sm-2 text-right text-success"><label class="checkbox-inline"><input type="checkbox" name="delete_vo_id[{$_id}]" value="{$_id}" /></label></div>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="edit_option[{$_id}]" value="{$_data['vo_option']|escape}" maxlength="80" />
				</div>
			</div>
{/foreach}
			<div class="form-group" id="new-option">
				<div class="col-sm-2 text-right text-success"><a href="javascript:;" id="add-option" class="btn btn-info btn-sm" role="button"><i class="fa fa-plus"></i> 新增选项：</a></div>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="new_option[]" value="" maxlength="80" />
				</div>
			</div>
    	</div>
	</div>
{if in_array('v_minchoices', $voteFunctions)}
	<div class="form-group">
		<label for="v_minchoices" class="col-sm-2 control-label">最少选项数：</label>
		<div class="col-sm-10">
			<input type="number" class="form-control" id="v_minchoices" name="v_minchoices" value="{$vote['v_minchoices']}" min="1" max="999" />
		</div>
	</div>
{/if}
{if in_array('v_maxchoices', $voteFunctions)}
	<div class="form-group">
		<label for="v_maxchoices" class="col-sm-2 control-label">最多选项数：</label>
		<div class="col-sm-10">
			<input type="number" class="form-control" id="v_maxchoices" name="v_maxchoices" value="{$vote['v_maxchoices']}" min="1" max="999" />
		</div>
	</div>
{/if}
{if in_array('v_ismulti', $voteFunctions)}
	<div class="form-group">
		<label for="ismulti_{$vote['v_ismulti']}" class="col-sm-2 control-label">是否为多选：</label>
		<div class="col-sm-10">
	{foreach $ismulti AS $_id => $_name}
			<label class="radio-inline"><input type="radio" id="ismulti_{$_id}" name="v_ismulti" value="{$_id}"{if $vote['v_ismulti']==$_id} checked="checked"{/if} /> {$_name}</label>
	{/foreach}
		</div>
	</div>
{/if}
{if in_array('v_isopen', $voteFunctions)}
	<div class="form-group">
		<label for="isopen_{$vote['v_isopen']}" class="col-sm-2 control-label">是否为开放：</label>
		<div class="col-sm-10">
	{foreach $isopen AS $_id => $_name}
			<label class="radio-inline"><input type="radio" id="isopen_{$_id}" name="v_isopen" value="{$_id}"{if $vote['v_isopen']==$_id} checked="checked"{/if} /> {$_name}</label>
	{/foreach}
		</div>
	</div>
{/if}
{if in_array('v_inout', $voteFunctions)}
	<div class="form-group">
		<label for="inout_{$vote['v_inout']}" class="col-sm-2 control-label">是否对外开放：</label>
		<div class="col-sm-10">
	{foreach $inout AS $_id => $_name}
			<label class="radio-inline"><input type="radio" id="inout_{$_id}" name="v_inout" value="{$_id}"{if $vote['v_inout']==$_id} checked="checked"{/if} /> {$_name}</label>
	{/foreach}
		</div>
	</div>
{/if}

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">保存</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{literal}
<script type="text/template" id="new_option_tpl">
<div class="form-group" id="new_option_<%= pIndex %>">
	<div class="col-sm-2 text-right text-success"><a href="javascript:_removeOption(<%= pIndex %>);" class="btn btn-danger btn-sm _2del_new" role="button"><i class="fa fa-times"></i></a></div>
	<div class="col-sm-10">
		<input type="text" class="form-control" name="new_option[]" value="" maxlength="80" />
	</div>
</div>
</script>
<script type="text/javascript">
function _removeOption(id) {
	jQuery('#new_option_'+id).remove();
}
jQuery(function(){
	jQuery('#add-option').click(function(){
		var j = jQuery('body');
		var pIndex = 1;
		if (typeof(j.data('pIndex')) != 'undefined') {
			pIndex = j.data('pIndex') + 1;
		}
		j.data('pIndex', pIndex);

		var str = txTpl("new_option_tpl", {"pIndex": pIndex});
		jQuery('#new-option').after(str);
	});
});
</script>
{/literal}

{include file='admincp/footer.tpl'}