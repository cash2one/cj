{include file="$tpl_dir_base/header.tpl"}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$askfor['af_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0;">

				<dt>申请人:</dt>
				<dd class="askfor_view">
					<strong class="label label-primary font12">{$askfor['m_username']|escape}</strong>
					&nbsp;&nbsp;
					<abbr title="申请时间"><span class="badge">{$askfor['_created']}</span></abbr>
				</dd>

			<dt>审批人:</dt>
			<dd>&nbsp;</dd>
			<dd>
				{if $askfor['aft_id'] != 0}
			{foreach $leav_list as $val}

			<div style="height:60px;"><div style="float:left;width:99px;height:60px; border-right:5px solid #D7D7D7;">第{$val}级审批人：</div>
				{foreach $proclist as $level}
					{if $level['afp_level'] == $val}
						<div style="width:80px;float:left;margin-left:15px;">
							<div style="width:80px;height:20px;text-align:center;line-height:20px;"><strong class="label label-primary font12">{$level['m_username']}</strong></div>
							<div style="width:80px;height:20px;text-align:center;color:{$level['_color']}">{$level['condition']}</div>

						</div>
					{/if}
				{/foreach}
			</div>
			{/foreach}
                <dd>
			<span style="margin-left:69px;">
			<img src="/admincp/static/images/ico.png" alt="" width="56px;">
			</span>
                </dd>
				{else if}
                <div style="height:60px;">
                    {foreach $proclist as $level}
						<div style="width:40px;float:left;">
							<div style="width:40px;height:20px;text-align:center;line-height:20px;"><strong class="label label-primary font12">{$level['m_username']}</strong></div>
							<div style="width:40px;height:20px;text-align:center;color:{$level['_color']}">{$level['_condition']}</div>
						</div>

				{/foreach}
                    </div>
				{/if}
			</dd>

			<dt>抄送人:</dt>
                <dd>{foreach $cs_uids as $va_cs}
                        <span class="label label-info font12">{$va_cs['m_username']}</span>
                    {/foreach}</dd>


			<dt>审批状态:</dt>
			<dd class="text-{$askfor['_status_class_tag']} askfor_view">{$askfor['_status']}</dd>

            <dt>审批内容:</dt>
                <dd>{$askfor['af_message']}</dd>
				<dt>审批图片:</dt>
				<dd>
                {foreach $att_list as $_at}
                    <div class="col-xs-1">
                        <a target="_blank" class="thumbnail"><img src="{$_at['imgurl']}" border="0" alt="" /></a>
                    </div>
                {/foreach}
				</dd>
</dl>
		</div>
	</div>
{*	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$askfor['af_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>申请人：</dt>
				<dd class="askfor_view">
					<strong class="label label-primary font12">{$askfor['m_username']|escape}</strong>
					&nbsp;&nbsp;
					<abbr title="申请时间"><span class="badge">{$askfor['_created']}</span></abbr>
				</dd>
{if $ccMemberList}
				<dt>抄送人：</dt>
				<dd class="askfor_view">
	{foreach $ccMemberList as $_uid => $_username}
					<span class="label label-info font12">{$_username}</span>

	{/foreach}
				</dd>
{/if}
				<dt>申请说明：</dt>
				<dd class="askfor_view">
					<blockquote class="m-0 font12">
						<h3 class="font12 text-bold"><strong>{$askfor['af_subject']|escape}</strong></h3>
						{$askfor['af_message']|escape}
					</blockquote>
{if $attach_list}
				<div class="row">
	{foreach $attach_list as $_at}
					<div class="col-xs-2">
						<a href="{$_at['url']}" target="_blank" class="thumbnail"><img src="{$_at['thumb']}" border="0" alt="" /></a>
					</div>
	{/foreach}
{/if}
				</dd>
{if $procMemberList}
				<dt>审批人：</dt>
				<dd class="askfor_view">
	{foreach $procMemberList as $_uid => $_username}
					<span class="label label-success font12">{$_username|escape}</span>
	{/foreach}
				</dd>
{/if}
				<dt>审批状态：</dt>
				<dd class="text-{$askfor['_status_class_tag']} askfor_view">{$askfor['_status']}</dd>
			</dl>
			</div></div>*}
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_proc" data-toggle="tab">
						<span class="badge pull-right"> {$proc_count} </span>
						审核进程&nbsp;
					</a>
				</li>

			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="list_proc">
					<table class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-15" />
							<col class="t-col-15" />
                            <col class="t-col-30"/>
							<col />
							<!-- <col /> -->
						</colgroup>
						<thead>
							<tr>
								<th>审批人</th>
								<th>审批状态</th>
                                <th>备注</th>
								<th>审批时间</th>

							</tr>
						</thead>

						<tbody>
{foreach $form_proclist as $_afp_id => $_afp}
							<tr>
								<td>{$_afp['m_username']}</td>
								<td class="text-{$_afp['_tag']}">{$_afp['_condition']}</td>
                                <td>{$_afp['afp_note']}</td>
								<td>{$_afp['_created']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="3">暂无审批记录</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>

			</div>

{include file="$tpl_dir_base/footer.tpl"}