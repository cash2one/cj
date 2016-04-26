{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<form class="form-horizontal font12" role="form" name="postform" action="" method="post" data-ng-app="ng.poler.plugins.pc">
				<input type="hidden" name="formhash" value="{$formhash}" />
				{if empty($tiku)}
				<div class="form-group">
					<label class="control-label col-sm-2 " for="id_title"></label>
					<div class="col-sm-6">
						<ul class="op-step clearfix">
							<li class="i-active col-sm-3">
								<em>1</em>
								<h3>题库设置</h3>
							</li>
							<li class="i-border col-sm-6"><em></em></li>
							<li class=" col-sm-3">
								<em>2</em>
								<h3>题目设置</h3>
							</li>
						</ul>
					</div>
					<div class="col-sm-4"></div>
				</div>
				{/if}

				<div class="form-group">
					<label class="control-label col-sm-2 " for="id_title">题库名称*</label>
					<div class="col-sm-6">
						<input type="text" class="form-control form-small" id="id_name" name="name" placeholder="不超过15个汉字" maxlength="64"  required="required" value="{$tiku.name}"/>
					</div>
					<div class="col-sm-4"></div>
				</div>

				<div class="form-group" id="btn-box">
					<div class="col-sm-2"></div>
					<div class="col-sm-6">
						
						{if empty($tiku)}<button type="button" class="btn btn-info" id="addtm_btn"  onclick="submit_form();">添加题目</button>{/if}
						&nbsp;&nbsp;<button type="submit" class="btn btn-primary" id="draft_btn">保存</button>
						{if $tiku}
						&nbsp;&nbsp;<button type="button" class="btn btn-default" onclick="javascript:history.go(-1);">返回</button>
						{/if}
					</div>
					<div class="col-sm-4"></div>
				</div>
				<input type="hidden" name="step" id="is_step" value="0">
				{if $tiku}<input type="hidden" name="id" value="{$tiku.id}">{/if}
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
function submit_form(){
	jQuery("input[name='step']").val(1);
	jQuery("#addtm_btn").attr("disabled", "true");
	document.forms['postform'].submit();
}

jQuery("#draft_btn").click(function() {
	$(this).attr("disabled", "true");
	document.forms['postform'].submit();
});

</script>

{include file="$tpl_dir_base/footer.tpl"}
