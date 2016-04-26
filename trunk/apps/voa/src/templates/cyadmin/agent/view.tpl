{include file='cyadmin/header.tpl'}
{include file='cyadmin/content/join/menu.tpl'}
<div class="panel panel-default font12">
    <div class="panel-body">
        <div class="profile-row">
            <div class="right-col">
                <div class="panel tl-body form-horizontal" >
                    <div class="form-group font12" style="margin-left:20px">
                        <label for="dateformat" class="col-sm-2">联系人名称：</label>
                        <div class="col-sm-10">
                            <b>{$view['fullname']}</b>
                        </div>
                    </div>

                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >联系人电话：</label>
                        <div class="col-sm-10">
                            {$view['telephone']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >邮箱：</label>
                        <div class="col-sm-10">
                            {$view['email']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >代理区域：</label>
                        <div class="col-sm-10">
                            {$view['region']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >公司名称：</label>
                        <div class="col-sm-10">
                            {$view['company']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >公司地址：</label>
                        <div class="col-sm-10">
                            {$view['company_address']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >公司简介：</label>
                        <div class="col-sm-10">
                            {$view['remark']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >提交ip：</label>
                        <div class="col-sm-10">
                            {$view['location_ip']}
                        </div>
                    </div>
                    <div class="form-group font12" style="margin-left:20px">
                        <label class="col-sm-2" >提交时间：</label>
                        <div class="col-sm-10">
                            {$view['created']}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-9">
                            <div class="row">
                                <div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
                                <div class="col-md-4"><a href="/enterprise/agent/delete/?acid={$view['aid']}" class="btn btn-default btn-primary col-md-9">删除</a></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
{include file='cyadmin/footer.tpl'}