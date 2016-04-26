{include file='mobile/header.tpl'}
<div class="ui-top-border"></div>
<form action="/askfor/new/{$aft_id}?handlekey=post" id="askfor_form" method="post" autocomplete="off">
	<input type="hidden" name="formhash" value="{$formhash}" />
	{if $cols_arr}
	    {foreach $cols_arr as $val}
		<input type="hidden" class="colss" value="{$val}">
	    {/foreach}
	{/if}
    <div class="ui-form"> 
	{if $template['subject']}
		<div class="ui-form-item ui-border-t">
			{$template['subject']}
			<input type="hidden" name="af_subject" id="af_subject" value="{$template['subject']}">
		</div>
	{else}
		{cyoa_input_text 
			attr_type='text' 
			title='审批主题' 
			attr_name='af_subject'
			attr_maxlength=15
			placeholder='输入审批主题，最多15个字' 
			attr_id='af_subject'
			attr_required='required'
		}
	 {/if}
		{cyoa_textarea 
			 title='审批内容'
			 attr_id='af_message'
			 attr_name='af_message'
			 attr_placeholder='输入审批内容，最多500个字'
			 attr_maxlength=500
			 attr_rows=3
			 attr_required='required'
		}    
		{if $template['cols']}
			{foreach $template['cols'] as $col}
			    {if $col['type'] <= 2}
				{cyoa_input_text 
					attr_name="cols[{$col['afcc_id']}]"
					title="{$col['name']|escape}"
                    attr_placeholder =($col['required'] == 1) ? '输入内容（必填）' : '输入内容'
                    attr_type=($col['type'] == 2) ? 'tel' : 'text'
                    attr_pattern = ($col['type'] == 2) ? '^[.0-9]*$' : ''
					attr_required=($col['required'] == 1) ? 'required' : null
					attr_class = "cols"
				} 
			    {elseif $col['type'] == 3}
				{cyoa_select
					 title="{$col['name']|escape}"
					 attr_id ="cols{$col['afcc_id']}"
					 attr_name="cols[{$col['afcc_id']}]"
					 attr_options=$dates
					 attr_required=($col['required'] == 1) ? 'required' : null
					 attr_class = "cols"
				 }
				 <input type="hidden" >
			    {elseif $col['type'] == 4}
				{cyoa_select
					 title="{$col['name']|escape}"
					 attr_name="cols[{$col['afcc_id']}]"
					 attr_options=$times
					 attr_required=($col['required'] == 1) ? 'required' : null
					 attr_id ="cols{$col['afcc_id']}"
					 attr_class = "cols"
				 }
			    {else}   

				 {cyoa_input_datetime
					attr_value=$datetime
					title="{$col['name']|escape}"
					attr_name="cols[{$col['afcc_id']}]"
					attr_required=($col['required'] == 1) ? 'required' : null
					div_id ="cols{$col['afcc_id']}"
					attr_class = "cols"
				   }
			    {/if}
		{/foreach}
		{/if}
		
	    {if $template['upload_image'] == 1}
	    {cyoa_upload_image
		allow_upload=1
		min=1
		max=6
	    }
	    {/if}
	    {if $aft_id == 0}
	    {cyoa_user_selector title='审批人' user_max=1 id="approve_div"}
	    {else}
	    <div class="ui-form-item ui-border-t">
			<label>审批人</label>
			<input name="af_subject" value="{$approvers}" type="text" disabled>
		</div>
	    {/if}
	    {cyoa_user_selector user_input='copy_uids' user_max=5 title='抄送人' id="copy_div"}
	     <div class="clearfix"></div>
	</div>
    
	<div class=" ui-btn-group-tiled ui-btn-wrap">
	    <input class="ui-btn-lg ui-btn-primary" type="submit" id="send" value="提交">
	</div>
</form>

{literal}
<script type="text/javascript">
var data_array = new Array();
require(["zepto", "underscore", "submit", "frozen"], function($, _, submit) {

    
    var $atrr = $('.colss');
	if ($atrr.length > 1) {
	$('.colss').each(function(i){
		  data_array[i] = $(this).attr('value');
		})
	}

	$('#send').on('click',function(e){
        /* 数字类型的输入判断 */
        if ($("input[type=tel]").length > 0) {
            $("input[type=tel]").each(function(){
                var tel_input = $(this).val();
                if (isNaN(tel_input)) {
                    $.tips({content:'输入类型不正确'});
                    return false;
                }
            })
        }

	var datetime_array = new Array();
		var subject = $.trim($('#af_subject').val());
		var message = $.trim($('#af_message').val());
		if (subject == '' ) {
			$.tips({content:'主题不能为空'});
			return false;
		}else if (message == ''){
			$.tips({content:'内容不能为空'});
			return false;  
		}else {
			var uids = $('#uids').val();
			if (uids == '') {
				$.tips({content:'请选择审批人'});
				return false;
			} 
		}
		
		if (data_array.length > 1) {
			var sdate = $("#"+data_array[0]).val();
			var edate = $("#"+data_array[1]).val(); 
			if (edate <= sdate) {
				$.tips({content:'结束时间不得低于开始时间'});
				return false;
			}
		}
		
		// 日期时间判断
		var date_time = $('._input_datetime_value');
		if (date_time.length > 1) {
			$('._input_datetime_value').each(function(i){
				datetime_array[i] = $(this).val();
			})

			var d1 = datetime_array[0].replace(/\-|:| /g, ""); 
			var d2 = datetime_array[1].replace(/\-|:| /g, "");  
			if (d2 <= d1) {
				$.tips({content:'结束时间不得低于开始时间'});
				return false;
			}
		}

	});
	
	var sbt = new submit();
	sbt.init({"form": $("#askfor_form")});

});

</script>
{/literal}

{include file='mobile/footer.tpl'}