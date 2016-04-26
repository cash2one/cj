{include file='mobile/header.tpl' css_file='app_news.css'}

<div class="ui-top-border"></div>
{if isset($type)}
<div  class="news-content-watermark" style="width: 100%; position: absolute;z-index:9999"></div>
{/if}
<form id="content">
	<input type="hidden" name="action" value="{$action}" />
	<input type="hidden" name="ne_id" value="{$result.ne_id}" />
	<input type="hidden" name="formhash" value="{$formhash}" />
<div class="ui-form">
	{cyoa_input_text attr_type='text'
		attr_placeholder='最多输入64个字符' title='标题' attr_name='title' attr_id='id-input'
		attr_class="news-title" attr_max="64" attr_value=$result.title attr_disabled= $type
	}
	{cyoa_textarea title='摘要'
		attr_name='summary' attr_placeholder="最多输入120个字符"
		attr_maxlength="120" attr_value=$result.summary attr_disabled= $type
	}

</div>
<div class="ui-form">
	<div class="ui-form-item ui-form-item-order ui-form-item-link">
		<label>类型</label>
		<p>请选择类型</p>
		<select id="id_nca_id" name="nca_id" class="form-control form-small" data-width="auto"  required="required" {if isset($type)}disabled="disabled" {/if}>
			<option value="" selected="selected">请选择类型</option>
			{foreach $categories as $_key => $_val}
				<optgroup label="{$_val['name']}">
					{if isset($_val['nodes'])}
						{foreach $_val['nodes'] as $_sv}
							<option value="{$_sv['nca_id']}" {if $result['nca_id'] == $_sv['nca_id']} selected="selected"{/if}>{$_sv['name']}</option>
						{/foreach}
					{else}
						<option value="{$_val['nca_id']}" {if $result['nca_id'] == $_val['nca_id']} selected="selected"{/if}>{$_val['name']}</option>
					{/if}
				</optgroup>
			{/foreach}
		</select>
	</div>

</div>
<div class="ui-form content">
	{cyoa_textarea title='正文'
		attr_id='textarea'
		attr_name='content'
		attr_placeholder="请输入公告内容"
		attr_value=$result.content
		attr_disabled= $type
	}
	<div class="upload">
		{cyoa_upload_image
			title='上传图片'
			attr_id='upload_image'
			name='atids'
			attachs=$result.cover
			progress=1
			min=0
			max=5
            description ="封面"
        }
		<span class="news-pic-state" {if $action=='edit'} style="display: none"{/if}>(第一张图片为封面图)</span>
		<div class="clearfix"></div>
	</div>
	<div class="ui-form-item ui-form-item-switch ui-border-t">
		<label for="#" class="news-label-width">封面图片显示在正文中</label> 
		<label for="#" class="ui-switch"> 
			<input type="checkbox" name="is_text" value='1' {if $result['is_cover']}checked="checked"{/if} {if isset($type)}disabled="disabled" {/if}/>
		</label>
	</div>
</div>

<div class="ui-form">
	{if $result.is_secret ==1 }
		{cyoa_input_switch
			title="消息保密"
			attr_id="is_secret"
			attr_name="is_secret"
			attr_value= 1
			open=1
			attr_disabled= $type
		}
	{else}
		{cyoa_input_switch
		title="消息保密"
		attr_id="is_secret"
		attr_name="is_secret"
		attr_value= 1
		open=0
		attr_disabled= $type
		}
	{/if}
	{if $result.is_comment == 1 }
		{cyoa_input_switch
		title="允许评论"
		attr_id="is_comment"
		attr_name="is_comment"
		attr_value= 1
		open=1
		attr_disabled= $type
		}
	{else}
		{cyoa_input_switch
		title="允许评论"
		attr_id="is_comment"
		attr_name="is_comment"
		attr_value= 1
		open=0
		attr_disabled= $type
		}
	{/if}
	
	{if $result.is_like == 1 }
		{cyoa_input_switch
		title="允许点赞"
		attr_id="is_like"
		attr_name="is_like"
		attr_value= 1
		open=1
		attr_disabled= $type
		}
	{else}
		{cyoa_input_switch
		title="允许点赞"
		attr_id="is_like"
		attr_name="is_like"
		attr_value= 1
		open=0
		attr_disabled= $type
		}
	{/if}

</div>

<div class="ui-form">
	{cyoa_user_selector
		title='阅读权限'
		user_input="m_uids"
        description = "全公司"
		div_class='ui-form-item ui-form-contacts'
		dp_name='选择部门'
		users=$result.right_users
		dps=$result.right_dps
        id = 'member_check'
		selectall=1
	}
	{if $result.is_message ==1 }
		{cyoa_input_switch
		title="消息提醒"
		attr_id="is_push"
		attr_name="is_push"
		attr_value=1
		open=1
		attr_disabled= $type
		}
	{else}
		{cyoa_input_switch
		title="消息提醒"
		attr_id="is_push"
		attr_name="is_push"
		attr_value=1
		open=0
		attr_disabled= $type
		}
	{/if}
</div>
<div class="ui-form">
	{if $result.is_check ==1 }
		{cyoa_input_switch
			title="发送预览"
			attr_id="is_check"
			attr_name="is_check"
			attr_value= 1
			attr_disabled= $type
		}
	{else}
		{cyoa_input_switch
			title="发送预览"
			attr_id="is_check"
			attr_name="is_check"
			attr_value= 1
			attr_disabled= $type
		}
	{/if}
	<div class="check_content" >
		{cyoa_textarea title='预览说明'
			attr_id='check_summary_box'
			attr_name='check_summary'
			attr_maxlength="120"
			attr_placeholder="最多输入120个字符"
			attr_disabled= $type
		}
		{cyoa_user_selector title='预览人' id="check_box"  user_input="check_id"}
	</div>
</div>
<div class="check_tip">
	注:发送预览后该公告会保存为草稿状态
</div>
{if empty($type)}
<div class="ui-btn-wrap">
	<button type="button" class="ui-btn-lg ui-btn-primary" id="send">发布</button>
</div>
    {/if}
</form>
<script type="text/javascript">
    // 定时处理阅读权限是否选择
    var t1 = window.setInterval(check_mp,1000);
    function check_mp() {
        var m_uids = $("input[name=m_uids]").val();
        var cd_ids = $("input[name=cd_ids]").val();

        // 默认 全公司阅读
        if (m_uids != '' || cd_ids != '') {
            $('#member_check p').html('&nbsp;');
            return false;
        } else {
            $('#member_check p').html('全公司');
        }
    }

    // 将第一张上传的图片 定义为封面
    var t2 = window.setInterval(check_upload, 500);
    function check_upload() {
        var atids = $("input[name=atids]").val();
        if (atids == "") {
            $(".upload-time").hide();
        } else {
            $(".upload-time").show();
        }

    }
</script>

<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _, fz) {
	
	$(function() {
		var option = $('option').not(function(){ return !this.selected });
		var checktext = option.text();
		$('#id_nca_id').prev().empty();
		$('#id_nca_id').prev().html(checktext);
		{if $result.cover_url}
		    $('._uploader_image_box').append('<div class="ui-badge-wrap"> <img src="{$result.cover_url}" alt="" border="0" class="_uploader_preview" > <div class="ui-badge-cornernum _uploader_remove" >-</div> <input name="temp-img" value="{$result.cover_url}" type="hidden"/></div>');
		{/if}
        // huang add
        var hah = $("#textarea").val();
        hah = hah.replace('<br />','/n');
        $("#textarea").val(hah);

	})
	if($('#is_check:checked').val() == 1){
		$('.check_content').show();
		$('#send').text('发布预览');
	}
	$('#is_check').on('click',function(){
		$('.check_content').toggle();
		$('#check_summary').val('');
		$('#check_uid').val('');
		$('#check_box ._addrbook_list').empty();
		var val = $('#is_check:checked').val();
		console.log(val);
		if(val == null){
			$('#send').text('发布');
		} else {
			$('#send').text('发布预览');
		}

	})

	$('#id_nca_id').on('change',function(){
		var option = $('option').not(function(){ return !this.selected })
		var checktext = option.text();
		$('#id_nca_id').prev().empty();
		$('#id_nca_id').prev().html(checktext);
	})
	
	//发送
	$('#send').on('click',function(){

		if($('input[name=m_uids]').val() == -1){
			$("input[name=cd_ids]").val(-1);
		}
		var _form = $("form").serialize();
		if(!$('input[name="temp-img"]')){
			var atids = $('input[name="atids"]').val();
			if(atids == '') {
				$.tips({
					content:'封面图片不能为空',
					stayTime:2000,
					type:"warn"
				});
				return false;
			}
		}
		if($('#send').text() == '发布预览'){
			if($('input[name="check_id"]').val() == ''){
				$.tips({
					content:'预览人不能为空',
					stayTime:2001,
					type:"warn"
				});
				return false;
			}
		}
		$.ajax({
			'type': 'POST',
			'url': '/api/news/post/addcontent',
			'data': _form,
			beforeSend: function(){
				el=$.loading({ content:'正在发送...' })
			},
			success: function(data){
				el.loading('hide');
				var obj = data.result;
				if (data.errcode == 0) {
					msg = obj.tips;
				}else {
					msg = data.errmsg;
				}
				var dlg = $.dialog({
					title:'',
					content:msg,
					button:["确认"]
				});
				dlg.on("dialog:action", function(e) {
					//获取类型
					var navtitle = $('#id_nca_id').val();
					if (data.errcode == 0) {
						var option = $('option').not(function(){ return !this.selected });
						var checktext = option.text();
						window.location.href = '/h5/index.html?#/app/page/news/news-list?nca_id='+ obj.result.nca_id+'&navtitle='+checktext;
					}

				});
				el.loading("hide");

			},
			error: function(){
				el.loading('hide');
			}
		});
	});
	
})
</script>

{include file='mobile/footer_news.tpl'}

