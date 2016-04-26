{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="mr_name" class="col-sm-2 control-label text-danger">会议室名称 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_name" name="mr_name" placeholder="会议室名称，如：1号会议室" value="{$meetingRoom['mr_name']|escape}" maxlength="30" required="required" />
		</div>
	</div>
	<div class="form-group">
		<label for="mr_address" class="col-sm-2 control-label text-danger">会议室地点 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_address" name="mr_address" placeholder="会议室地点，如：XXX路XXX号xxx室" value="{$meetingRoom['mr_address']|escape}" maxlength="255" required="required" />
		</div>
	</div>
	<div class="form-group">
		<label for="mr_address" class="col-sm-2 control-label text-danger">会议室楼层 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mr_floor" name="mr_floor" placeholder="请填写会议室楼层，如1" value="{$meetingRoom['mr_floor']|escape}" maxlength="255" required="required" />
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
		<label for="mr_timestart" class="col-sm-2 control-label">可预定时间</label>
		<div class="col-sm-10">
			<input type="time" class="form-control from-input-90" id="mr_timestart" name="mr_timestart" value="{$meetingRoom['mr_timestart']}" />
			&nbsp;&nbsp;至&nbsp;&nbsp;
			<input type="time" class="form-control from-input-90" id="mr_timeend" name="mr_timeend" value="{$meetingRoom['mr_timeend']}" />
			<span class="help-block">可预定时间格式为24小时制，如：09:30</span>
		</div>
	</div>
	{if $mr_id}
	<div class="form-group">
		<label for="mr_timestart" class="col-sm-2 control-label">二维码</label>
		<div class="col-sm-10">
			<a href="/admincp/office/meeting/mrlist/pluginid/{$pluginid}/?act=qrcode&id={$mr_id}&code={$meetingRoom['mr_code']}">
				<img style="float:left;width:310px;height:143px;" src="/admincp/office/meeting/mrlist/pluginid/{$pluginid}/?act=qrcode&id={$mr_id}&code={$meetingRoom['mr_code']}"/>
			</a>
			<ul id="tip">
				<li>二维码：您可以在会议室张贴带会议室信息的二维码，这样您的员工进入会议室时通过扫描二维码进行“签到”操作。</li>
				<li>会议的主持人在开会前也可以通过扫描二维码进行“会议室确认”操作 和 “提前退场”操作。</li>
				<li>提示：会议室列表可查看,下载二维码。</li>
			</ul>
		</div>
	</div>
	{else}
	<div class="form-group">
		<label for="mr_timestart" class="col-sm-2 control-label">二维码样例</label>
		<div class="col-sm-10">
			<img src="/admincp/static/images/meeting_qrcode.png" style="float:left;width:310px;height:143px;"/>
			<ul style="float:left;width:300px;line-height:20px;">
				<li>二维码：您可以在会议室张贴带会议室信息的二维码，这样您的员工进入会议室时通过扫描二维码进行“签到”操作。</li>
				<li>会议的主持人在开会前也可以通过扫描二维码进行“会议室确认”操作 和 “提前退场”操作。</li>
				<li>提示：此二维码只是样例,添加好会议室后在会议室列表可查看,下载实际二维码。</li>
			</ul>
		</div>
	</div>
	{/if}
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">{if $mr_id}编辑{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>
<style>
#tip {
	float:left;
	width:330px;
	font-size: 14px;
}
#tip li {
	margin: 12px 0;
}
</style>
{include file="$tpl_dir_base/footer.tpl"}