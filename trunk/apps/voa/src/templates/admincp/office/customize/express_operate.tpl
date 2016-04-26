{include file="$tpl_dir_base/header.tpl"}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$formActionUrl}?ac=express_operate">
<div id="container_main">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">编辑</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="formhash" value="{$formhash}" /> 
                <input type="hidden" name="expid" value="{$express['expid']}">
                <div class="form-group">
                    <label class="col-sm-2 control-label">快递类型</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="exptype" name="exptype" value="{$express['exptype']}" required="required">
                        </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">快递费用</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="expcost" value="{$express['expcost']}" required="required" placeholder="快递费用已元为单位">
                        </div>
                </div>
            </div>
            
            
            <div class="modal-footer">
                <a href="?ac=express" class="btn btn-default">返回</a>
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </div>
    </div>
 </div>
</form>

<script type="text/javascript">
	$(function(){
		$('#form-adminer-edit').submit(function(){
			var exptype=$('#exptype').val();
			if(exptype.length == 0){
				alert('请输入快递类型！');
				return false;
			}
		});
	});	
</script>

{include file="$tpl_dir_base/footer.tpl"}
