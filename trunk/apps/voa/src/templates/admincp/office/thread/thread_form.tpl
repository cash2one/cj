{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" id="edit-form"  method="post" action="{$formActionUrl}">
	            <input type="hidden" name="formhash" value="{$formhash}" />
				<div class="form-group">
					<label class="control-label col-sm-2" for="id_rights">标题</label>
					<div class="col-sm-9">
						<input type="text" class="form-control form-small" id="subject" name="subject" placeholder="最多输入15个汉字"  maxlength="15"  required="required"/>			
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2" for="id_rights">照片</label>
					<div class="col-sm-9" id="contact_container">
					{cycp_upload_multi
					inputname='cover2_id'
					tip='(推荐尺寸 480x230)'
					max = 5
				    }
					</div>
			
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2" for="id_content">内容</label>
					<div class="col-sm-9">
					 <textarea class="form-control" rows="5"  name="message" required="required" maxlength="500" ></textarea>
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-6">
						<button type="submit" class="btn btn-primary">{if $ta_id}编辑{else}发布{/if}</button>
						&nbsp;&nbsp;
						<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-6">
						<input type="checkbox" class=" form-small" id="ck_msg" name="ck_msg" value="1"  />&nbsp;&nbsp;发送消息提醒(通过微信企业号)	通知所有员工	
					</div>
				</div>
		</form>
	</div>
</div>


{include file="$tpl_dir_base/footer.tpl"}
