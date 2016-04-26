{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">
            类型列表
            <button class="btn-add" style="float:right;"><i class="fa fa-plus"></i>
                新增类型</button>
        </div>

    </div>
    <form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
        <input type="hidden" name="formhash" value="{$formhash}"/>
        <table class="table table-striped table-bordered table-hover font12">
            <colgroup> 
                <col class="t-col-12"/>
                <col class="t-col-12"/>
                <col class="t-col-10"/> 
            <thead>
                <tr> 
                    <th>类型名称</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead> 
            <tbody id="tbody_id"> 
            </tbody>
        </table>
    </form>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="fade-title">新增类型</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="txt_statu">类型名称</label>
                    <input type="text" name="name" class="form-control" placeholder="最多输入15个汉字" maxlength="15">
                </div>      
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>关闭</button>
                    <button type="button" id="btn_submit" class="btn btn-save" data-dismiss="modal"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>保存</button> 

                    <input type="hidden" id="qc_id">
                </div>
            </div> 
        </div>
    </div>
</div>



<!-- 弹出确认删除 -->
<div class="modal modal-alert modal-warning fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-warning"></i>
            </div>
            <div class="modal-title"></div>
            <div class="modal-body">
                <span class=" text-info">确定要删除吗？</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-delete" data-dismiss="modal">确定</button>
                <input type="hidden" name="delete-qc-id"/>
            </div>
        </div>
    </div>
</div>

{literal}
    <script type="text/template" id="tpl-list">
        <% if (!jQuery.isEmptyObject(list)) { %>
        <% $.each(list,function(n,val){ %>
        <tr> 
        <td><%=val['name']%></td>
        <td><%=val['created']%></td>

        <td>
        <% if (val['created'] != '固定分类') {%>
        <a href="javascript:;" class="btn-edit"><i class="fa fa-edit"></i>
        编辑</a>
        <input class="org_id" type="hidden" value="<%=val['qc_id']%>"/>
        <input class="org_name" type="hidden" value="<%=val['name']%>"/>
        <a href="javascript:;" class="btn-confirm-delete" af-id="<%=val['qc_id']%>" style="color:#337AB7;"><i class="fa fa-times" style="color:#337AB7;"></i>删除</a>
        <% } %>
        </td>
        </tr>

        <% }) %>
        <% }else{ %>
        <tr>
        <td colspan="8" class="warning">
        暂无类型信息
        </td>
        </tr>
        <% } %>

    </script>
{/literal}

<script type="text/javascript">

    $(function () {
        //获取数据
        $.get('/Questionnaire/Apicp/Classify/List', function (data) {
            $('#tbody_id').html(txTpl('tpl-list', data.result));

            //编辑按钮
            $('.btn-edit').on('click', function () {
                $('#qc_id').val($(this).siblings('.org_id').val());
                $('input[name=name]').val($(this).siblings('.org_name').val());
                $('#fade-title').text('修改问卷类型');
                $('#editModal').modal('show');

            });

            //新增按钮
            $('.btn-add').on('click', function () {
                $('input[name=name]').val('');
                $('#qc_id').val('');
                $('#fade-title').text('新增问卷类型');
                $('#editModal').modal('show');
            });

            $('.btn-save').on('click', function () {
                var qcid = $('#qc_id').val();
                var name = $('input[name=name]').val();

                if (name.length == 0) {
                    $('input[name=name]').focus();
                    return false;
                }

                $.post('/Questionnaire/Apicp/Classify/Add', "qcid=" + qcid + "&name=" + name, function (data) {
                    if (data.errcode == 0) {
                        $('#editModal').modal('hide');
                        $('input[name=name]').val('');
                        window.location.reload();
                    } else {
                        alert(data.errmsg);
                        $('input[name=name]').val('');
                        return false;
                    }
                });
            });
            
            //弹出删除确认框
            $('.btn-confirm-delete').on('click', function () {
                $('#confirmModal').modal('show');
                $('input[name=delete-qc-id]').val($(this).siblings('.org_id').val());
            });
            
            //确认删除
            $('.btn-delete').on('click', function(){
                var qcid = $('input[name=delete-qc-id]').val();
                $.post('/Questionnaire/Apicp/Classify/Delete', "qcid=" + qcid, function (data) {
                    if (data.errcode == 0) {
                        window.location.reload();
                    }
                });
            });
        });
    });

</script>

{include file="$tpl_dir_base/footer.tpl"}