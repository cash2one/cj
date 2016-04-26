{include file="$tpl_dir_base/header.tpl"}

<form id="edit-form" role="form" method="post" action="{$formActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" /> 
<div id="container_main">
<div class="">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel" id="edit">
                <div class="panel-heading">
                    <span class="panel-title">编辑</span>
                    <div class="panel-heading-controls">
                        <div class="panel-heading-text"></div>
                    </div>
                </div>
                <div class="panel-body">
                    <table id="js-form-customize" class="table table-bordered table-striped" style="margin-bottom: 0px;">
                        <tbody>
                        
                        <tr id="_tr_9">
						    <td width="35%">官方头像</td>
						    <td width="65%" style="text-align: left;">
						    {cycp_upload
							inputname='at_id'
						    showimage=1
						    attachid={$p_sets['offical_img']}
							}
    						</td>
					    </tr>
                        
                        <tr id="_tr_6">
						    <td width="35%">官方昵称</td>
						    <td width="65%">
					        <input id="offical_name" type="text" name="offical_name" class="form-control" reg="" value="{$p_sets['offical_name']}" placeholder="">
							</td>
                        </tr>
                        
                        <tr id="_tr_11">
						    <td width="35%">热门设置</td>
						    <td style="text-align: left;" >
						    <div class="padding-xs-vr">
						        <label class="checkbox-inline">
				                    <input type="radio" id="hot_key" name="hot_key" value="likes" {if $p_sets['hot_key']=='likes'}checked="checked"{/if}  class="px"} >
				                    <span class="lbl">点赞数</span>
	                			</label>
	                			
	                		    <label class="checkbox-inline">
				                    <input type="radio" id="hot_key" name="hot_key" value="replies" {if $p_sets['hot_key']=='replies'}checked="checked"{/if}  class="px"} >
				                    <span class="lbl">评论数</span>
	                			</label>
						    </div>
                            
               			    <div class="padding-xs-vr">
				                <div class="input-group">
									<span class="input-group-addon">≥</span>
									<input type="text" class="form-control" name="hot_value" value="{$p_sets['hot_value']}" placeholder="请输入数字">
								</div>
							</div>
                            </td>
                        </tr>
                        
                        
                        <tr id="_tr_11">
						    <td width="35%">精选设置</td>
						    <td style="text-align: left;" >
						    
						    <div class="padding-xs-vr">
						        <label class="checkbox-inline">
				                    <input type="radio" id="choice_key" name="choice_key" value="likes" {if $p_sets['choice_key']=='likes'}checked="checked"{/if}  class="px"} >
				                    <span class="lbl">点赞数</span>
	                			</label>
	                			
	                		    <label class="checkbox-inline">
				                    <input type="radio" id="choice_key" name="choice_key" value="replies"  {if $p_sets['choice_key']=='replies'}checked="checked"{/if}  class="px"} >
				                    <span class="lbl">评论数</span>
	                			</label>
						    </div>
						    
               			    <div class="padding-xs-vr">
				                <div class="input-group">
									<span class="input-group-addon">≥</span>
									<input type="text" class="form-control" name="choice_value" placeholder="请输入数字" value="{$p_sets['choice_value']}">
								</div>
							</div>
                            </td>
                        </tr>
      
                        
					    </tbody>
			        </table>
				</div>
				<div class="panel-footer">
					<button type="submit" class="btn btn-primary">保存</button>
				    &nbsp;&nbsp;
				    <a href="javascript:history.go(-1);" class="btn btn-default">取消</a>
				</div>
			</div>
		</div>
	</div>
  </div>
</div>
</form>

<script type="text/javascript">
	$(function(){
		$('#edit-form').submit(function(){
			var choice=$('input:radio[name="choice_key"]:checked').val();
			var hot=$('input:radio[name="hot_key"]:checked').val();
			if(choice == hot){
				alert('热门设置和精选设置只能选择一种标准（即点赞数或评论数）！');
				return false;
			}
			
			var offic_name = $('#offical_name').val();
			if(offic_name.length == 0){
				alert('请输入官方昵称！');
				return false;
			}
			
			
		});
	});	
</script>

{include file="$tpl_dir_base/footer.tpl"}