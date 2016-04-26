{include file='admincp/header.tpl'}

<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="mr_name" class="col-sm-2 control-label text-danger">会议室名称 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_name" name="mr_name" placeholder="会议室名称" value="{$meetingRoom['mr_name']|escape}" maxlength="30" required="required" />
		</div>
	</div>
	<div class="form-group">
		<label for="mr_address" class="col-sm-2 control-label text-danger">会议室地址 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_address" name="mr_address" placeholder="会议室地址，如：601室" value="{$meetingRoom['mr_address']|escape}" maxlength="255" required="required" />
		</div>
	</div>
	<div class="form-group">
		<label for="mr_galleryful" class="col-sm-2 control-label">容纳人数</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_galleryful" name="mr_galleryful" placeholder="容纳人数，如：可容纳15-20人" value="{$meetingRoom['mr_galleryful']|escape}" maxlength="255" />
		</div>
	</div>
	<div class="form-group">
		<label for="mr_device" class="col-sm-2 control-label">会议室可用的设备</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_device" name="mr_device" placeholder="会议室可用的设备，如：投影仪、电话等" value="{$meetingRoom['mr_device']|escape}" maxlength="255" />
		</div>
	</div>
	<div class="form-group">
		<label for="mr_volume" class="col-sm-2 control-label">会议室规模</label>
		<div class="col-sm-10">
			<select id="mr_volume" name="mr_volume" class="selectpicker bla bla bli" data-width="auto">
				<option value="0">请选择……</option>
{foreach $meetingRoomVolume as $v=>$n}
				<option value="{$v}"{if $meetingRoom['mr_volume']==$v} selected="selected"{/if}>{$n}</option>
{/foreach}
			</select>
		</div>
	</div>
	<div class="form-group">
		<label for="mr_timestart" class="col-sm-2 control-label">可预定时间</label>
		<div class="col-sm-10">
			<input type="time" class="form-control from-input-90" id="mr_timestart" name="mr_timestart" value="{$meetingRoom['mr_timestart']}" />
			&nbsp;&nbsp;至&nbsp;&nbsp;
			<input type="time" class="form-control from-input-90" id="mr_timeend" name="mr_timeend" value="{$meetingRoom['mr_timeend']}" />
			<span class="help-block">可预定时间格式为24小时制，如：09:30</span>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">{if $mr_id}编辑{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{include file='admincp/footer.tpl'}