{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12" id="tbody-afsubject"></h3>
	</div>
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0;">

			<dt>申请人:</dt>
			<dd class="askfor_view" id="tbody-username">

			</dd>

			<dt>审批人:</dt>
			<dd>&nbsp;</dd>
			<dd id="tbody-splist">

			</dd>

			<dt>抄送人:</dt>
			<dd id="tbody-cslist">

			</dd>


			<dt>审批状态:</dt>
			<dd class="" id="af_condition"></dd>

			<dt>审批内容:</dt>
			<dd id="tbody-afmessage">

			</dd>
			<dt id="field-name">自定义字段:</dt>
			<dd id="tbody-field">

			</dd>
			<dt>审批图片:</dt>
			<dd id="tbody-imglist">

			</dd>
		</dl>
	</div>
</div>

<ul class="nav nav-tabs font12" >

	<li class="active">
		<a href="#list_proc" data-toggle="tab" id="tbody-count">

		</a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="list_proc">
		<table class="table table-striped table-hover table-bordered font12 table-light">
			<colgroup>
				<col class="t-col-15"/>
				<col class="t-col-15"/>
				<col class="t-col-30"/>
				<col/>
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

			<tbody id="tbody-proclist">

			</tbody>
		</table>
	</div>
	<input type="hidden" value="{$af_id}" id="af_id">
</div>
<literal>
<script type="text/javascript">

	$(function () {
		var af_id = $('#af_id').val();
		$.ajax({
			url: '/Askfor/Apicp/Askfor/View',
			dataType: 'json',
			//jsonp: 'callback',
			data: 'af_id=' + af_id,
			type: 'get',
			success: function (result) {
				if (result.errcode == 0) {
					$('#tbody-splist').html(txTpl('tpl-splist', result.result));
					$('#tbody-cslist').html(txTpl('tpl-cslist', result.result));
					$('#tbody-afmessage').html(txTpl('tpl-afmessage', result.result));
					$('#tbody-afsubject').html(txTpl('tpl-afsubject', result.result));
					$('#tbody-imglist').html(txTpl('tpl-imglist', result.result));
					$('#tbody-proclist').html(txTpl('tpl-proclist', result.result));
					$('#tbody-username').html(txTpl('tpl-username', result.result));
					$('#tbody-count').html(txTpl('tpl-count', result.result));
					$('#af_condition').html(result.result.askfor._status);
					$('#af_condition').attr('class', 'text-'+result.result.askfor._tag+' askfor_view');
					if(result.result.askfor.aft_id == 0){
						$('#field-name').hide();
						$('#tbody-field').hide();
					}else{
						$('#tbody-field').html(txTpl('tpl-field', result.result));
					}
				} else {
					alert(result.errcode + ' : ' + result.errmsg);
				}
			}
		});
	});
</script>
</literal>
<script type="text/template" id="tpl-splist">

	<% if (askfor['aft_id'] != 0){ %>
		<% for(var val = 1;val <= leav_list.length; val++){ %>
			<div style="height:60px;">
			<div style="float:left;width:99px;height:60px; border-right:5px solid #D7D7D7;">第<%=val%>级审批人：</div>
				<% if (!jQuery.isEmptyObject(sp_list)) { %>
			<% $.each(sp_list, function(n,level){ %>
				<% if (level.afp_level == val){ %>
					<div style="width:100px;float:left;margin-left:15px;">
					<div style="width:100px;height:20px;text-align:center;line-height:20px;"><strong
						class="label label-primary font12"><%=level['m_username']%></strong></div>
						<div style="width:100px;height:20px;text-align:center;color:<%=level['_color']%>">
							<% if(level['_condition'] != undefined){ %>
							<%=level['_condition']%>
							<% } %>
				</div>
				</div>
				<% } %>
			<% }) %>
				<% } %>
	</div>
		<% } %>

			<span style="margin-left:69px;">
			<img src="/admincp/static/images/ico.png" alt="" width="56px;">
			</span>

	<% } else { %>
	<div>
		<% if (!jQuery.isEmptyObject(sp_list)) { %>
		<% $.each(sp_list, function(k, level){ %>
		<div style="width:80px;float:left;">
			<div style="width:80px;height:20px;text-align:center;line-height:30px;"><strong
						class="label label-primary font12"><%=level['m_username']%></strong></div>
			<div style="width:80px;height:20px;text-align:center;color:<%=level['_color']%>"><%=level['_condition']%>
			</div>
		</div>
		<% }) %>
		<% } %>
	</div>
	<div style="clear:both"></div>

	<% } %>
</script>
<script type="text/template" id="tpl-cslist">
	<% if (!jQuery.isEmptyObject(cs_list)) { %>
		<% $.each(cs_list, function(k, cs){ %>
			<span class="label label-info font12"><%=cs['m_username']%></span>
		<% }) %>
	<% } %>
</script>
<script type="text/template" id="tpl-afmessage">
	<div style="word-wrap:break-word;word-break:break-all;"><%=askfor['af_message']%></div>
</script>
<script type="text/template" id="tpl-afsubject">
	<strong><%=askfor['af_subject']%></strong>
</script>

<script type="text/template" id="tpl-imglist">
	<% if (!jQuery.isEmptyObject(att_list)) { %>
	<% $.each(att_list, function(k, img){ %>

		<div class="col-xs-1">
			<a target="_blank" class="thumbnail" href="<%=img['imgurl']%>"><img src="<%=img['imgurl']%>" border="0" alt=""/></a>
		</div>
	<% }) %>
	<% } %>
</script>

<script type="text/template" id="tpl-proclist">
	<% if (!jQuery.isEmptyObject(form_proclist)) { %>
	<% $.each(form_proclist, function(k, proc){ %>
		<tr>
			<td><%=proc['m_username']%></td>
			<td class="text-<%=proc['_tag']%>"><%=proc['_condition']%></td>
			<td><%=proc['rafp_note']%></td>
			<td><%=proc['_created']%></td>
		</tr>

	<% }) %>
	<% } else { %>
	<tr class="warning">
		<td colspan="4">暂无审批记录</td>
	</tr>
	<% } %>
</script>

<script type="text/template" id="tpl-username">
	<strong class="label label-primary font12"><%=askfor['m_username']%></strong>
	&nbsp;&nbsp;
	<abbr title="申请时间"><span class="badge"><%=askfor['_created']%></span></abbr>
</script>
<script type="text/template" id="tpl-field">
	<% if (!jQuery.isEmptyObject(custom_data)) { %>
	<% $.each(custom_data, function(k, field){ %>
	<tr>
		<td class="label label-primary font12 text-right" style="float:right;"><%=field['name']%>:</td>
		<td style="text-align:left;word-wrap:break-word;word-break:break-all;">&nbsp;<%=field['value']%></td>
	</tr>
	<% }) %>
	<% } %>
</script>
<script type="text/template" id="tpl-count">
	<span class="badge pull-right" > <%=proc_count%></span>
	审核进程&nbsp;
</script>
{include file="$tpl_dir_base/footer.tpl"}