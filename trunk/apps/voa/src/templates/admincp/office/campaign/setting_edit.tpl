{include file="$tpl_dir_base/header.tpl"}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$formActionUrl}?act=setting_edit">
<div id="container_main">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">编辑活动类型</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="formhash" value="{$formhash}" /> 
                <input type="hidden" name="typeid" value="{$typeid}">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">分类名称</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="title" maxlength="12" name="title" placeholder="最多不超过12个汉字" value="{$title|escape}">
                            </div>
                    </div>
            </div>
            <div class="modal-footer">
                <a href="?ac=cate" class="btn btn-default">返回</a>
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </div>
    </div>
 </div>
</form>

<script type="text/javascript">
    $(function(){
        $('#form-adminer-edit').submit(function(){
            var classname=$('#title').val();
            if(classname.length == 0){
                alert('请输入分类名称！');
                return false;
            }
        });
    }); 
</script>
<script type="text/javascript">
 function edit_cate( ) {
    var url = "?act=setting_edit&typeid="+$(t).attr('data-typeid');
    window.location.href=url;  
 }
</script>

{include file="$tpl_dir_base/footer.tpl"}
