{include file='cyadmin/header.tpl'}

<script type="text/javascript">
  $(function () {
    $('.status-switch').iphoneSwitch(


      {
        start_state: function (o) {
            if ($(o).attr('state') == '0') {
                return 'on';
            } else {
                return 'off';

            }
        },
        switch_off_container_path: '{$static_url}images/iphone_switch_container_on.png',
        switch_path: '{$static_url}images/iphone_switch.png',
        switch_on_container_path: '{$static_url}images/iphone_switch_container_off.png',
        switched_off_callback: function(o) {
            var field = $(o).parents('.status-switch').attr('field');
            $(o).parents('.status-switch').attr('state', 1);
            $(o).parents('.status-switch').parents('table').find('.editable,.editable-textarea').unbind();


            $.ajax({
                url: "{$edit_url_base}",
                type: "POST",
                data: 'act=profilesave&update_value=1&field='+field,
            });
      },
      switched_on_callback: function(o) {
        var field = $(o).parents('.status-switch').attr('field');
        $(o).parents('.status-switch').attr('state', 0);
        init_editable($(o).parents('.status-switch').parents('tr').find('td.editable'), 'text');
        $.ajax({
            url: "{$edit_url_base}",
            type: "POST",
            data: 'act=profilesave&update_value=0&field='+field,
        });
      },
      disable: function (o) {
        if ($(o).hasClass('all-finish')) {
            var finish = 1;
                $('.status-switch').each(function() {
                    if ($(this).attr('state') == 0 && !$(this).hasClass('all-finish')) {
                        finish = 0;


                    }
                });
            if (!finish) {
                alert('你有还有完成的业务');
                return true;
            } else {
                return false;
            }
        }
        if ($('.all-finish').attr('state') == 1) {
            return true;
        } else {
            return false;
        }
      }
      });


    init_editable('td.editable', 'text');
    init_editable('td.editable-textarea', 'textarea');


    $('.app-submit').click(function(){
        var agent = $.trim($(this).parents('tr').find('.editable').html());
        if (agent != '0' && agent != '') {
            if (confirm('确认提交?')) {
               $(this).parents('tr').find('.appstatus_text').html('已提交');
               $.ajax({
                        url: "{$edit_url_base}",
                        type: "POST",
                        data: 'act=appsave&update_value=1&field=ea_appstatus&ea_id='+$(this).attr("ea_id"),
                        success: function (text) {
                            alert(text);
                        }
                    });
                $(this).parents('tr').find('.editable,.editable-textarea').unbind();
                $(this).remove();
            }
        } else {
            alert('请先填写你的Agent ID');


            return false;
        }
    });
  });
  function init_editable(o, field_type) {
  /*
      if (typeof o == 'object') {
          if (o.length > 1) {
              $(o).each(function () {
                    init_editable($(this), field_type);
              });
              return false;
          }
      }*/
      $(o).editInPlace({
            url: "{$edit_url_base}",
            params: function (o){
                if (o.attr("ea_id")) {
                    var ea_id = o.attr("ea_id");
                    return "act=appsave&ea_id="+ea_id;
                } else {
                    return "act=profilesave";
                }
            },        field_type: field_type,
            saving_image: "{$static_url}images/loader.gif",


            default_text: '',
        });
    }
  </script>
<div class="panel panel-default enterprise-company-edit">
<div class="panel-heading">列表
<button type="button" class="close"><span
	class="glyphicon glyphicon glyphicon-chevron-down"></span></button>
</div>
<div class="panel-body">
<table class="table table-bordered">

	<thead>
		<tr>
			<th>注册日期</th>
			<th>公司名称</th>
			<th>行业</th>
			<th>城市</th>
			<th>代理商</th>
			<th>注册人姓名</th>
			<th>注册人职位</th>
			<th>邮箱</th>
			<th>手机</th>
			<th>域名</th>
			<th>操作</th>
		</tr>
	</thead>
	<div id="bg" style="width:100%; display:none; position:absolute;background:#000;z-index:998;top:0;left:0;  height:2000px;opacity:0.6; "></div>
	
	<div style="position:fixed;width:100%;height:150px;z-index:9999;display:none;" id="box_edit">
<div style="margin:0 auto;width:700px;height:320px;display:none;background:#ffffff;;" id="box_border">
<div style="width:650px;height:45px;line-height:45px;text-align:center;background:#F4F5F9;float:left;" >发送消息</div>
<div style="width:50px;height:45px;float:left;font:20px 黑体;padding-top:10px;background:#F4F5F9;" id="esc">×</div>
<form action="{$message_url_base}" method="post" style="clear:both;" id="sendform">
<input type="hidden" name="ep_id" value="{$profile['ep_id']}">

<!--
<textarea name="content" cols=78 rows=6></textarea>
 -->
<p style="padding-left:100px;">标题： <input type="text" name="title" style="width:530px;" class="form-control" id="send_title"></p>
<p style="padding-left:100px;">内容：</p>
<p style="padding-left:100px;"><textarea name="content" cols=85 rows=6 id="send_content"></textarea></p>
<p style="padding-left:100px;line-height:45px;background:#F4F5F9;"><a id="es" class="btn btn-default">取消</a><button class="btn btn-success" style="margin-left:50px;">发送</button></p>
</form>
</div>

</div>
	<tbody>
		<tr>
			<td>{$profile['_created']}</td>
			<td>{$profile['ep_name']}</td>
			<td {if $profile['ep_statusep'] == 0}class="editable"
				{/if} field="ep_industry">{$profile['ep_industry']}</td>
			<td {if $profile['ep_statusep'] == 0}class="editable"
				{/if} field="ep_city">{$profile['ep_city']}</td>
			<td field="ep_agent"><select id="id-agent" name="ep_agent">
				<option value='?epid={$profile["ep_id"]}&agent= {if $profile["ep_agent"] != " "}&oldagent={$profile["ep_agent"]}{/if}' >无</option>
				{foreach $account_list as $key=>$val}

				<option value='?epid={$profile["ep_id"]}&agent={$key}{if $profile["ep_agent"] != " "}&oldagent={$profile["ep_agent"]}{/if}' {if $profile['ep_agent']==$key}selected{/if}>{$val}</option>
				{/foreach}
			</select></td>
			<td>{$profile['ep_contact']}</td>
			<td>{$profile['ep_contactposition']}</td>
			<td>{$profile['ep_email']}</td>
			<td>{$profile['ep_mobilephone']}</td>
			<td>{$profile['ep_domain']}</td>
			<td>
			<div class="status-switch" state="{$profile['ep_statusep']}"
				field="ep_statusep"></div>
			</td>
		</tr>
	</tbody>
</table>
<table class="table table-bordered">
	<thead>
		<tr>
			<th>企业号名称</th>
			<th>企业号用户名</th>
			<th>企业号密码</th>
			<th>corp ID</th>
			<th>corpSECRET</th>
			<th>管理员手机号</th>
			<th>管理员姓名</th>
			<th>管理员部门</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_wxname">{$profile['ep_wxname']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_wxuname">{$profile['ep_wxuname']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_wxpasswd">{$profile['ep_wxpasswd']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_wxcorpid">{$profile['ep_wxcorpid']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_wxcorpsecret">{$profile['ep_wxcorpsecret']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_adminmobile">{$profile['ep_adminmobile']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_adminrealname">{$profile['ep_adminrealname']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_admindepartment">{$profile['ep_admindepartment']}</td>
		</tr>
	</tbody>
	<thead>
		<tr>
			<th>信鸽 ACCESS ID</th>
			<th>信鸽 ACCESS KEY</th>
			<th>信鸽 SECRET KEY</th>
			<th colspan="2">微信token</th>
			<th colspan="2">二维码</th>

			<th>操作</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_xgaccessid">{$profile['ep_xgaccessid']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_xgaccesskey">{$profile['ep_xgaccesskey']}</td>
			<td {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_xgsecretkey">{$profile['ep_xgsecretkey']}</td>
			<td colspan="2" {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_wxtoken">{$profile['ep_wxtoken']}</td>
			<td colspan="2" {if $profile['ep_statuswx'] == 0}class="editable"
				{/if} field="ep_qrcode">{$profile['ep_qrcode']}</td>
			<td>
			<div class="status-switch" state="{$profile['ep_statuswx']}"
				field="ep_statuswx"></div>
			</td>
		</tr>
	</tbody>
</table>
<form action="{$edit_url_base}" method="post" id="editform">
<input type="hidden" name="formedit" value="1">
<input type="hidden" name="ep_id" value="{$profile['ep_id']}">
<table class="table table-bordered">


	<thead>
		<tr>
			<th>支付金额</th>
			<th>购买期限</th>
			<th>购买空间</th>
			<th>开始时间</th>
			<th>结束时间</th>
			<th>付款状态</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><input type="text" id="ep_money" name="ep_money" style="width:100px" value="{$profile['ep_money']}">(元)</td>
			<td>

			<select name="ep_deadline">

				{foreach $date as $key=>$val}
				<option value="{$key}" {if $profile['ep_deadline']==$key}selected{/if}>{$val}</option>
				{/foreach}
			

			</select>
			</td>
			<td>
			<input type="text" name="ep_space" style="width:100px" value="{$profile['ep_space']}" id="ep_space">(GB)
			</td>
			<td><input type="date" name="ep_start" {if $profile['ep_start'] != null}value="{$profile['_ep_start']}"{/if}></td>
			<td><input type="date" name="ep_end" {if $profile['ep_end'] != null}value="{$profile['_ep_end']}"{/if}></td>
			<td><!-- <div class="status-switch" state="{$profile['ep_paystatus']}"
				field="ep_paystatus"></div>-->
				<label><input type="radio" name="ep_paystatus" value="0" {if $profile["ep_paystatus"]==0}checked{/if}>未付款</label>
				<label><input type="radio" name="ep_paystatus" value="1" {if $profile["ep_paystatus"]==1}checked{/if}>已付款</label>
				</td>
			<td><input type="submit" value="提交"></td>
		</tr>
	</tbody>
</table>
</form>
<div class="alert alert-info">
<div class="row">
<div class="col-sm-3" ><strong>发消息通知商家</strong></div>
<div class="col-sm-9"><button class="pull-right" id="me_sub">写消息</button></div>
</div>
</div>
<table class="table table-bordered table-condensed table-hover">
	<thead>
		<tr>
			<th>图标</th>
			<th>应用名</th>
			<th>插件ID</th>
			<th>Agent ID</th>
			<th>应用描述</th>
			<th>状态</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
		<!--应用维护状态：0待建立，1待删除，2已建立，3已删除，4待关闭，5已关闭-->
		{foreach $list as $_ea_id=>$item}
		<tr>
			<td><img src="{$item['ea_icon']}" border="0" alt="" width="24"
				height="24" /></td>
			<td>{$item['ea_name']}</td>
			<td>{$item['oacp_pluginid']}</td>

			<td{if $item['ea_appstatus'] < 3}class="editable"
			ea_id="{$item['ea_id']}"
			field="ea_agentid"{/if}>{$item['ea_agentid']}</td>
			<td{if $item['ea_appstatus'] < 3}class="editable-textarea"
			ea_id="{$item['ea_id']}"
			field="ea_description"{/if}>{$item['ea_description']}
			<td class="appstatus_text">{$item['ea_appstatus_text']}</td>
			<td>{if $item['ea_appstatus'] < 3}
			<button type="button" ea_id="{$item['ea_id']}"
				class="btn btn-default app-submit">提交</button>
			{/if}</td>
	

		</tr>
		{foreachelse}
		<tr>
			<td colspan="7" class="warning">暂无数据</td>
		</tr>
		{/foreach}
	</tbody>
</table>
<div class="alert alert-success">
<div class="row">
<div class="col-sm-3"><strong>全部完成</strong></div>
<div class="col-sm-9"><span class="pull-right status-switch all-finish"
	state="{$profile['ep_statusall']}" field="ep_statusall"></span></div>
</div>
</div>

</div>
</div>
<script>
jQuery(function () {
	jQuery('#id-agent').change(function () {


		$.ajax({
			url:'/cyadmin/enterprise/epedit'+this.value,
			type:'get',
			success:function(data){
				alert('修改成功');
				window.location.href = window.location.href;
			}
			});
	});
		var re = /^[0-9]+$/gi;
		$('#editform').submit(function(){
	
		if (!re.test($('#ep_money').val())) {
			alert('付款金额必须是数字');
			return false;
		}
		var res = /^[0-9]+$/gi;
		
		if (!res.test($('#ep_space').val())) {
			alert('购买空间必须为数字');
			return false;
		}

		});
		$('#me_sub').on('click',function(){
		

			$('#box_border').css('display','block');
			$('#bg').css('display','block');
			$('#box_edit').css('display','block');
			});
		$('#esc').on('click',function(){
			$('#box_border').css('display','none');
			$('#bg').css('display','none');
			$('#box_edit').css('display','none');
			});
		$('#es').on('click',function(){
			$('#box_border').css('display','none');
			$('#bg').css('display','none');
			$('#box_edit').css('display','none');
			});
		$('#sendform').submit(function(){
			if($('#send_title').val()==''){
					alert('请填写标题');
					return false;
				}
			if($('#send_content').val()==''){
				alert('请填写内容');
				return false;
			}
			});
});
</script>

{include file='cyadmin/footer.tpl'}
