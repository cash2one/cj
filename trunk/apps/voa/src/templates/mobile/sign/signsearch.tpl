{include file='mobile/header.tpl' css_file='app_sign.css'}

<div class="ui-tab">
	<div class="ui-btn-group-tiled ui-padding-bottom-0 ui-padding-top-0 sale-ui-title" >

		{if $permission != 1}
			<p class="ui-btn-lg ui-btn-primary clearfix btn-width ui-selector _sel" style="background:#FFFFFF;color:#4C4C49;width:100%;"><span class="choose">{$current}</span>
				<select name="update" id="update" >
					{foreach $month as $_key => $_val}
						<option  value="{$_key}">{$_val}</option>

					{/foreach}
				</select>
				<i id="arrow1" class="label-tag sale-label-tag label-tag-down"></i>
			</p>
		{else}
			<p class="ui-btn-lg ui-btn-primary clearfix btn-width ui-selector _sel" style="background:#FFFFFF;color:#4C4C49;"><span class="choose">{$current}</span>
				<select name="update" id="update" >
					{foreach $month as $_key => $_val}
						<option  value="{$_key}">{$_val}</option>

					{/foreach}
				</select>
				<i id="arrow1" class="label-tag sale-label-tag label-tag-down"></i>
			</p>
		{/if}



		{if $permission == 1}
			<div class="sale-title-border">
				<div class="sale-title-border">
				</div>
			</div>

			<p id="m_selector" class="ui-btn-lg ui-btn-primary clearfix btn-width" style="background:#FFFFFF;">
				<a  class="ui-icon-add" style="width:100%;height:100%;color:#4C4C49;">选人
					<i class="label-tag sale-label-tag label-tag-down"></i></a>
			</p>
		{/if}
	</div>
	<div class="ui-select-content ui-form" style="display: none;">
		<div class="ui-form sale-ui-nowrap">
			{cyoa_input_datetime
			attr_value=' '
			title="选择日期"
			attr_name="udate"
			}
			<input type="hidden" id="cm_uid" name="cm_uid" value="{$cm_uid}" />
		</div>
		<div class="ui-btn-group-tiled ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
			<button type="button" id="cancel" name="cancel" class="ui-btn-lg">取消</button>
			<button  id="sure" name="sure" class="ui-btn-lg ui-btn-primary">确定</button>
		</div>
	</div>
	<div style="width:100%;margin:10px 5px;background:#F5F4F3;" id="cal">

	</div>
	<div id="list_active">
	</div>

	<div>
		<div id="info">

		</div>
		<div id="work_time"></div>
		<br />
		<br />
		<br />
		<input type="hidden" id="udat" value="{$udate}">

	</div>
	{literal}
		<script type="text/javascript">
			require(["zepto", "underscore", "showlist", "submit", "addrbook", "frozen"], function($, _, showlist, submit, addrbook) {

				var sl = new showlist();
				function getinfo(udate,cm_uid){

					if(udate == ''){
						udate = $('#udat').val();
					}
					$.ajax({
						'url':'/api/sign/get/cal?udate='+udate+'&m_uid='+cm_uid,
						'type':'get',
						'success':function(result){
							$('#cal').html('');
							$('#cal').append(result);

							_click();
						}
					});
				}

				var udate = $('#udat').val();

				var cm_uid = $("#cm_uid").val();
				getinfo(udate,cm_uid);
				_post('','');
				function _click(){

					$('#ta_cal td span').on('click', function(){
						$('#ta_cal td span').css('border', '1px solid #F5F4F3');
						var year = $(this).attr('dat');
						var day = $(this).html();

						$(this).css('border','1px solid #374234');
						_post(year,day);
					})
				}
				function _post(year,day){
					if(year != ''){
						var udate = year+'-'+day;
						if(udate == undefined){
							udate = '';
						}
					}else{
						udate = ''
					}

					var cm_uid = $("#cm_uid").val();
					$.ajax({
						'url':'/api/sign/get/signsearch?m_uid='+cm_uid+'&udate='+udate,
						'type':'get',
						'success':function(result){
							$('#info').html(_.template($('#tpl-list').html(), result.result));
							$('#work_time').html(_.template($('#tpl-li').html(), result.result));
						}
					});

				}

				$('#update').change(function () {
					var udate = this.value;
					$('.choose').text(udate);
					var cm_uid = $("#cm_uid").val();
					getinfo(udate, cm_uid);
				});
				//选人
				var ab = new addrbook();
				ab.show({
					"dist": $("#addrbook"),
					"src": $("#m_selector"), // 触发对象,
					"ac" : "byuser",
					"tabs": {
						"user": {
							"name": "选择用户",
							"input": $("#cm_uid")
						}
					},
					"cb": function() {
						var udate = $("._input_datetime_value").val();
						if(udate == undefined){
							udate = ' ';
						}
						var cm_uid = $("#cm_uid").val();
						getinfo(udate,cm_uid);
					}
				});
			});

		</script>

	{/literal}
	{literal}
		<script type="text/template" id="tpl-list">


			<% if(!_.isEmpty(list)){ %>
			<% $.each(list,function(n,val){ %>
			<ul style="height:50px;background:#FFFFFF;width:100%;border-bottom:1px solid #EEEDEB;">
				<li><% if(val['sr_type'] == 1){%>上班 <% }else{ %>下班<%}%><%=val['_signtime'] %></li>
				<li style="color:#DAD7D2;"><%=val['sr_address'] %></li>
			</ul>
			<% }) %>
			<% }else{　%>
			暂无记录
			<% } %>

		</script>
		<script type="text/template" id="tpl-li">

			<ul style="background:#FFFFFF;width:100%;border-bottom:1px solid #EEEDEB;">
				<li>工作时长 <%=work_time%></li>
				<% if(!_.isEmpty(detail_list)){ %>
				<% $.each(detail_list,function(n,val){ %>
				<li style="color:#DAD7D2;">备注:<%=val['sd_reason']%></li>
				<% }) %>
				<% }else{　%>
				未添加备注
				<% } %>
			</ul>

		</script>
	{/literal}
	{include file='mobile/footer.tpl' SHOWIMG=1}
