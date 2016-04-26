{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}

<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" id="edit-form"  method="post" action="" data-ng-app="ng.poler.plugins.pc" onsubmit="return checkQuestion();">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<input type="hidden" name="isedit" value="{$isedit}" />
			<input type="hidden" name="answer" value="{$ti['answer']}" />
			{if empty($ti)&&$isedit!=1}
			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_title"></label>
				<div class="col-sm-2"></div>
				<div class="col-sm-4">
					<ul class="op-step clearfix">
						<li class="col-sm-3">
							<em>1</em>
							<h3>题库设置</h3>
						</li>
						<li class="i-border col-sm-6"><em></em></li>
						<li class="col-sm-3 i-active">
							<em>2</em>
							<h3>题目设置</h3>
						</li>
					</ul>
				</div>
				<div class="col-sm-4"></div>
			</div>
			{/if}
			<div class="form-group">
				<label class="control-label col-sm-2" for="title">题目类型</label>
				<div class="col-sm-9">
					<select name="type" class="form-control" onchange="on_sel_type(this.value)">
						{foreach $types as $k => $v}
						<option value="{$k}"{if $k == $ti['type']} selected{/if}>{$v}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="orderby">题目排序*</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="orderby" name="orderby" value="{if empty($ti)}{$tiku['num']+1}{else}{$ti['orderby']}{/if}" maxlength="15"  placeholder="请输入正整数" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="id_score">题目分数*</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="id_score" name="score" value="{$ti['score']}" placeholder="请输入正整数" maxlength="15" required="required" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title">题目名称*</label>
				<div class="col-sm-9">
					<textarea  class="form-control form-small" id="id_title" name="title" placeholder="不超过500个汉字" maxlength="120" required="required" rows="4" >{$ti['title']}</textarea>
				</div>

			</div>
			<div id="options_wrap">
				<div id="option_list">
					{if $ti['options']}
						{foreach explode("\r\n",$ti['options']) as $_k => $_v}
					<div class="form-group">
						<label class="control-label col-sm-2">选项 {chr($_k+65)}</label>
						<div class="col-sm-9">
							<div class="col-sm-9">
								<input type="text" class="form-control form-small" name="options[]" value="{$_v}"  placeholder="不超过120个字" />
							</div>
							{if $_k>1}
							<div class="col-sm-3">
								<a class="text-danger" href="javascript:;" onclick="delOption({$_k});"><i class="fa fa-times"></i> 删除</a>
							</div>
							{/if}
						</div>
					</div>
						{/foreach}
					{/if}
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2"></label>
					<div class="col-sm-9">
					<input type="button" value="添加选项" onclick="addOption();" class="btn btn-default">
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_summary">答案*</label>
				<div class="col-sm-9" id="answerType0">
					{if $ti['options']}
						{foreach explode("\r\n",$ti['options']) as $_k => $_v}
						<i class="exam-check{if chr($_k+65)==$ti['answer']} i-check{/if}">{chr($_k+65)}</i>
						{/foreach}
					{/if}
				</div>
				<div class="col-sm-9" id="answerType1">
					<textarea  class="form-control form-small" placeholder="请输入答案" maxlength="120" rows="4" >{str_replace("\r\n",'',$ti['answer'])}</textarea>
				</div>
				<div class="col-sm-9" id="answerType2">
					<i class="exam-check{if '对'==$ti['answer']} i-check{/if}">对</i>
					<i class="exam-check{if '错'==$ti['answer']} i-check{/if}">错</i>
				</div>
				<div class="col-sm-9" id="answerType3">
					{if $ti['options']}
						{$answer_arr=explode("\r\n",$ti['answer'])}
						{foreach explode("\r\n",$ti['options']) as $_k => $_v}
						<i class="exam-check{if in_array(chr($_k+65),$answer_arr)} i-check{/if}">{chr($_k+65)}</i>
						{/foreach}
					{/if}
				</div>
				
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2"></label>
				<div class="col-sm-9">
					<p class="help-block text-danger"></p>
				</div>
			</div>
			
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-6">
					<input name="save" type="submit" value="保存" class="btn btn-primary">
					&nbsp;&nbsp;{if empty($ti)}
					<input name="savego" type="submit" value="保存并添加下一题" class="btn btn-primary">
					&nbsp;&nbsp;{/if}
					<a href="{$back_url}" role="button" class="btn btn-default">返回</a>
				</div>
			</div>
			{if $ti}<input type="hidden" name="id" value="{$ti['id']}" />{/if}
			{if $tiku_id}<input type="hidden" name="tiku_id" value="{$tiku_id}" />{/if}
		</form>
	</div>
</div>
{if $ti}
	<script type="text/javascript">
	jQuery('h1').html('<i class="fa fa-edit page-header-icon"></i>&nbsp;&nbsp;修改题目');
	</script>
{/if}



<script type="text/javascript">
var q_type={if $ti['type']}{$ti['type']}{else}0{/if};
var option_key=0;
var options_arr={json_encode(explode("\r\n",$ti['options']))};
var answer_arr={json_encode(explode("\r\n",$ti['answer']))};

function on_sel_type(value) {
	if(value==0||value==3){
		$('#options_wrap').show();
	}else{
		$('#options_wrap').hide();
	}
	$('div[id^="answerType"]').hide();
	$('#answerType'+value).show();
	q_type=value;

}
on_sel_type(q_type);

{if $ti}
option_key={count(explode("\r\n",$ti['options']))};
if(q_type==1||q_type==2){
	option_key=0;
}
{else}
for (var i = 0; i < 2; i++) {
	addOption();
};
{/if}


$(document).on("click",".exam-check",function(){
	var obj=$(this).parent();
	if(obj.attr('id')!='answerType3'){
		obj.children('i').removeClass('i-check');
	}
	if($(this).hasClass('i-check')){
		$(this).removeClass('i-check');
	}else{
		$(this).addClass('i-check');
	}
});

function addOption(){
	if(option_key>25) return false;	
	var chr=String.fromCharCode(65+option_key);
	var delstr="";
	if(option_key>1){
		delstr='<div class="col-sm-3"><a class="text-danger" href="javascript:;" onclick="delOption('+option_key+');"><i class="fa fa-times"></i> 删除</a></div>';
	}

	var item='<div class="form-group"><label class="control-label col-sm-2">选项 '+chr+'</label><div class="col-sm-9"><div class="col-sm-9"><input type="text" class="form-control form-small" name="options[]" placeholder="不超过120个字" /></div>'+delstr+'</div></div>';

	$('#option_list').append(item);

	// 添加单选答案选项
	item='<i class="exam-check">'+chr+'</i>';
	$('#answerType0').append(item);
	$('#answerType3').append(item);

	option_key++;
}
function delOption(k){
	if(option_key>2){
		$('#option_list .form-group:eq('+k+')').remove();
		$('#option_list .form-group').each(function(i){
			$(this).children('label').html('选项 '+String.fromCharCode(65+i) );
			$(this).find('a').attr('onclick', 'delOption('+i+')' );
		});

		$('#answerType0 i:eq('+k+')').remove();
		$('#answerType0 i').each(function(i){
			$(this).html(String.fromCharCode(65+i) );
		});

		$('#answerType3 i:eq('+k+')').remove();
		$('#answerType3 i').each(function(i){
			$(this).html(String.fromCharCode(65+i) );
		});
		option_key--;
	}
}

function IsNum(s){
    if (s!=null && s!="")
    {
        return !isNaN(s);
    }
    return false;
}
// 是否正整数
function isInteger(obj) {
    return IsNum(obj) && obj%1 === 0;
}

{literal}
function checkQuestion(){
	var answer='';
	var is_empty=false;
	if(q_type==0){
		answer=$('#answerType0 .i-check').html();
	}else if(q_type==1){
		answer=$('#answerType1 textarea').val();
	}else if(q_type==2){
		answer=$('#answerType2 .i-check').html();
	}else if(q_type==3){
		var arr=[];
		$('#answerType3 .i-check').each(function(i){
			arr.push($(this).html());
		});
		answer=arr.join("\r\n");
		if(arr.length<2){
			alert("多选题答案必须大于1个");
			return false;  
		}
	}

	if(q_type==3||q_type==0){
		$("input[name='options[]']").each(function(){
			if($(this).val()==''){
				is_empty=true;
				return false;
			}
		});
		if(is_empty){
			alert("请填写选项"); 
			return false;  
		}
	}

	var score=$("#id_score").val();
	if(score<0||!isInteger(score)){
		alert("题目分数必须是大于0的整数"); 
		return false;  
	}
	if(answer==''||answer==undefined){
		alert('请输入或选择答案');
		return false;
	}
	$('input[name="answer"]').val(answer);
	return true;
}
{/literal}
</script>

{include file="$tpl_dir_base/footer.tpl"}