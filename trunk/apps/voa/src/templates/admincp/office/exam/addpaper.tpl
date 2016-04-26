{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<form class="form-horizontal font12" role="form" action="" method="post" data-ng-app="ng.poler.plugins.pc" onsubmit="return checkForm();">
				<input type="hidden" name="formhash" value="{$formhash}" />

				<div class="form-group">
					<label class="control-label col-sm-2" for="id_title"></label>
					<div class="col-sm-8">
						<ul class="op-step clearfix">
							<li class="i-active col-sm-2">
								<em>1</em>
								<h3>模式设置</h3>
							</li>
							<li class="i-border col-sm-3"><em></em></li>
							<li class="col-sm-2">
								<em>2</em>
								<h3>选择题目</h3>
							</li>
							<li class="i-border col-sm-3"><em></em></li>
							<li class="col-sm-2">
								<em>3</em>
								<h3>基本设置</h3>
							</li>
						</ul>
					</div>
					<div class="col-sm-1"></div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 " for="id_title">试卷名称*</label>
					<div class="col-sm-9">
						<input type="text" class="form-control form-small" id="id_name" name="name" placeholder="最多输入64个字符" maxlength="64"  required="required" value="{$paper.name}"/>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2" for="title">选题模式</label>
					<div class="col-sm-9">
						<select class="form-control" name="type" onchange="on_sel_type(this.value)">
							{foreach $types as $k => $v}
							<option value="{$k}"{if $k == $paper['type']} selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2" for="title">选择题库*</label>
					<div class="col-sm-9">
						{foreach $tikus as $k => $v}
						<div class="col-sm-3">
							<input type="checkbox" name="tiku[]" value="{$v.id}" onchange="update_count()" {if is_array($paper['tiku']) && in_array($k, $paper['tiku'])} checked{/if}>&nbsp;{$v.name} 
						</div>
						{/foreach}
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2" for="title"></label>
					<div class="col-sm-9">
						<div class="col-sm-12">
							<b id="use_all_wrap"{if $paper && $paper['type']!=0} style="display:none;"{/if}><input type="checkbox" name="use_all" value="1"{if $paper['use_all']==1} checked{/if}>&nbsp;使用所选题库的所有题目</b>
						</div>
					</div>
				</div>

				<div id="rulewrap" class="form-group"{if !$paper || $paper['type']==0} style="display:none;"{/if}>
					<label class="control-label col-sm-2" for="title">抽题规则设置</label>
					<div class="col-sm-9">
						
						
						<table class="form-tb">
							<tr>
								<td>单选题总数：<span id="dan_num"></span></td>
								<td>选择</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_DAN}][num]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_DAN]['num']!=''}{$paper['rules'][voa_d_oa_exam_ti::TYPE_DAN]['num']}{else}0{/if}"></td>
								<td>题，每题</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_DAN}][score]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_DAN]['score']}{$paper['rules'][voa_d_oa_exam_ti::TYPE_DAN]['score']}{else}1{/if}"></td>
								<td>分</td>
							</tr>
							<tr>
								<td>多选题总数：<span id="duo_num"></span></td>
								<td>选择</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_DUO}][num]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_DUO]['num']!=''}{$paper['rules'][voa_d_oa_exam_ti::TYPE_DUO]['num']}{else}0{/if}"></td>
								<td>题，每题</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_DUO}][score]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_DUO]['score']}{$paper['rules'][voa_d_oa_exam_ti::TYPE_DUO]['score']}{else}1{/if}"></td>
								<td>分</td>
							</tr>
							<tr>
								<td>判断题总数：<span id="pan_num"></span></td>
								<td>选择</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_PAN}][num]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_PAN]['num']!=''}{$paper['rules'][voa_d_oa_exam_ti::TYPE_PAN]['num']}{else}0{/if}"></td>
								<td>题，每题</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_PAN}][score]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_PAN]['score']}{$paper['rules'][voa_d_oa_exam_ti::TYPE_PAN]['score']}{else}1{/if}"></td>
								<td>分</td>
							</tr>
							<tr>
								<td>问答题总数：<span id="tian_num"></span></td>
								<td>选择</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_TIAN}][num]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_TIAN]['num']!=''}{$paper['rules'][voa_d_oa_exam_ti::TYPE_TIAN]['num']}{else}0{/if}"></td>
								<td>题，每题</td>
								<td><input type="text" class="form-control form-small" name="rules[{voa_d_oa_exam_ti::TYPE_TIAN}][score]" value="{if $paper['rules'][voa_d_oa_exam_ti::TYPE_TIAN]['score']}{$paper['rules'][voa_d_oa_exam_ti::TYPE_TIAN]['score']}{else}1{/if}"></td>
								<td>分</td>
							</tr>
						</table>

					</div>
				</div>

				<div class="form-group" id="btn-box">
					<div class="col-sm-offset-2 col-sm-9">
						<div class="row">
							<div class="col-md-4">
								<button type="submit" class="btn btn-primary  col-md-9" id="draft_btn">下一步</button>
							</div>
						</div>
					</div>
				</div>
				{if $paper}<input type="hidden" name="id" value="{$paper.id}">{/if}
			</form>
		</div>
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}
<script type="text/javascript">

var tikus = {json_encode($tikus)};
var tikuids = {json_encode(array_keys($tikus))};
var type = {if $paper}{$paper['type']}{else}0{/if};

var dan_num, duo_num, pan_num, tian_num;

function on_sel_type(val) {
	val == 0 ? jQuery('#rulewrap').hide() : jQuery('#rulewrap').show();
	val == 0 ? jQuery('#use_all_wrap').show() : jQuery('#use_all_wrap').hide();
	type = val;
	update_count();
}
on_sel_type(type);

function update_count() {
	// 自主选题不用更新规则
	if($('select[name="type"]').val() == 0) {
		return;
	}

	dan_num = 0, duo_num = 0, pan_num = 0, tian_num = 0;
	var ids = [];
	$("input[name='tiku[]']").each(function(index, obj) {
		if(obj.checked)
			ids.push(obj.value);
	});

	for(var i in ids) {
		var tiku = tikus[ids[i]];
		dan_num += parseInt(tiku['dan_num']);
		duo_num += parseInt(tiku['duo_num']);
		pan_num += parseInt(tiku['pan_num']);
		tian_num += parseInt(tiku['tian_num']);
	}

	$('#dan_num').html(dan_num);
	$('#duo_num').html(duo_num);
	$('#pan_num').html(pan_num);
	$('#tian_num').html(tian_num);
}

update_count();

function checkForm(){
	var err=false;
	
	var checkednum=0;
	$("input[name='tiku[]']").each(function(index, obj) {
		if(obj.checked)
			checkednum++;
	});

	if(checkednum==0){
		alert('请选择一个题库');
		return false;
	}

	if(type>0){

		var total=0;
		$('input[name*="[num]"]').each(function(i){
			
			var tt=parseInt($(this).parent().parent().find("span").html());
			var st=parseInt( $(this).val()?$(this).val():0 );
			if( st>tt ){
				err=true;
			}
			total+=st;
		});
		if (err){
			alert('选择的题数不能超过总题数,请设置抽题规则');
			return false;
		}
		if(total==0){
			alert('选择的题数不能全部为空');
			return false;
		}

		// 判断分数
		$('input[name*="[score]"]').each(function(i){
			if( $(this).val()<1 ){
				err=true;
			}
		});
		if (err){
			alert('分数必须大于0');
			return false;
		}
	}
	return true;
}
</script>
