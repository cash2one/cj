
<!-- 添加部门弹出框 -->
<div id="modal-add-department" class="modal fade" role="dialog" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">添加部门</h4>
            </div>
            <form name="form_department" id="form_department" method="post">
                <div class="modal-body no-padding">
                    <input type="hidden" name="up_id" value="" />
                    <input type="hidden" name="id" value="" />
                    <table class="table">
                        <tbody>
                        <tr>
                            <td class="text-left">部门名称</td>
                            <td>
                                <input type="text" class="form-control"  name="name" />
                                <span class="help-block" style="text-align: left">请填写部门完整名称</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-left">权限选择</td>
                            <td>
                                <select class="form-control" id="purview" name="purview">
                                    <option value="" selected="true" disabled="true">权限选择</option>
                                    <option value="1" >全公司</option>
                                    <option value="2" >仅本部门</option>
                                    <option value="3" >仅子部门</option>
                                </select>
                                <span class="help-block" style="text-align: left">
                                    全公司：可以看到全公司通讯录<br/>
                                    仅本部门：可以看到本部门及所有子部门通讯录<br/>
                                    仅子部门：可以看到所有子部门及下属组织的通讯录<br/>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2"><hr /></td>
                        </tr>
                        <tr>
                            <td class="text-left">排序号</td>
                            <td>
                                <input type="text" class="form-control" name="order" />
                             <span class="help-block" style="text-align: left">
                                  填写数字。用于显示部门先后顺序，数字小的排在前面
                             </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="submit" name="btn_submit" class="btn btn-info">提交</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 添加部门弹出框 -->

<!-- 删除部门弹出框 -->
<div id="modal-delete-department" class="modal modal-alert modal-danger fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-times-circle"></i>
            </div>
            <div class="modal-title"></div>
            <div class="modal-body">
                <span class=" text-info">确定要删除<span class="span_department"></span>吗？</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">取消</button>
                <button type="button" name="btn_delete" class="btn btn-danger" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>
<!-- 删除部门弹出框 -->