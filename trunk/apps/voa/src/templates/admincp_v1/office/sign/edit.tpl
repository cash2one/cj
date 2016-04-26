{include file='admincp/header.tpl'}

<div class="panel panel-default">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<dl class="dl-horizontal font12 vcy-dl-list">
			<dt>签到人：</dt>
			<dd><strong class="text-info">{$signRecord['m_username']|escape}</strong></dd>
			<dt>签到时间：</dt>
			<dd><strong class="text-info">{$signRecord['_signtime']}</strong></dd>
			<dt>签到 IP：</dt>
			<dd><strong class="text-info">{$signRecord['sr_ip']}</strong></dd>
			<dt>签到类型：</dt>
			<dd><strong class="text-info">{$signRecord['_type']}</strong></dd>
			<dt>签到状态：</dt>
			<dd><strong class="text-danger">{$signRecord['_status']}</strong></dd>
			<dt>记录最后更新时间：</dt>
			<dd>{$signRecord['_updated']}</dd>
			<dt><label for="id_sr_status" class="text-danger">重设签到状态为：</label></dt>
			<dd>
				<select id="id_sr_status" name="sr_status" class="selectpicker bla bla bli bootstrap-select-small font12" data-width="auto">
					<option value="{$signRecord['sr_status']}">不改变</option>
{foreach $signStatus as $_k => $_n}
	{if $_k != $signStatusSet['remove'] && $_k != $signStatusSet['unknown']}
					<option value="{$_k}"{if $_k==$signRecord['sr_status']} selected="selected"{/if}>{$_n}</option>
	{/if}
{/foreach}
				</select>
			</dd>
			<dt><label for="id_sd_reason">重设状态备注说明：</label></dt>
			<dd>
				<textarea id="id_sd_reason" name="sd_reason" rows="2" cols="9" class="form-control"></textarea>
				<span class="help-block">如果重新设置了签到状态，则必须填写备注说明</span>
			</dd>
			<dt><span class="space"></span></dt>
			<dd>
				<button type="submit" class="btn btn-primary">提交重设</button>
				<span class="space"></span>
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</dd>
		</dl>
		</form>
		<ul class="nav nav-tabs font12">
			<li class="active">
				<a href="#list_detail" data-toggle="tab">
					重设状态历史记录&nbsp;
				</a>
			</li>
		</ul>
		<br />
		<div class="tab-content">
			<div class="tab-pane active" id="list_detail">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-20" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>时间</th>
							<th>备注</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="2" class="text-right">{$detailMulti}</td>
						</tr>
					</tfoot>
					<tbody>
{foreach $detailList as $_id => $_data}
						<tr>
							<td>{$_data['_updated']}</td>
							<td>{$_data['_reason']}</td>
						</tr>
{foreachelse}
						<tr class="warning">
							<td colspan="2">暂无任何状态重设记录</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{include file='admincp/footer.tpl'}