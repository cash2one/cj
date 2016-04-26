{include file="$tpl_dir_base/header.tpl"}

<style type="text/css">
    .datepicker-orient-top{
        z-index: 999999!important;
    }
    .timepicker-orient-top{
        z-index: 999999!important;
    }
</style>

<div class="panel panel-default font12">
    <div class="panel-body">
        <form class="form-horizontal font12" role="form" id="edit-form"  method="post" action="{$formActionUrl}">
            <input type="hidden" name="formhash" value="{$formhash}" />
            <input type="hidden" name="ac" value="{$ac}" />

            <div class="form-group">
                <label class="control-label col-sm-2" for="title">活动主题</label>
                <div class="col-sm-9">
                    <input value="{$data['title']}" type="text" class="form-control form-small" id="title" name="title" placeholder="最多输入15个汉字"  maxlength="15"  required="required"/>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">开始时间</label>
                <script>
                    init.push(function () {
                        var options1 = {
                            todayBtn: "linked",
                            orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
                            startDate: new Date()
                        };
                        $('#start_data').datepicker(options1);
                        $('#start_time').timepicker({
                            showMeridian:false
                        });
                    });
                </script>
                <div class="col-sm-9">
                    <div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
                        <div style="width: 300px">
                            <input value="{$data['start_data']}" required="required" type="text" class="input-sm form-control" id="start_data" name="start_time[data]" placeholder="开始日期" style="width: 150px"/>
                            <input value="{$data['start_time']}" required="required" type="text" class="input-sm form-control" id="start_time" name="start_time[time]" style="width: 150px"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">结束时间</label>
                <script>
                    init.push(function () {
                        var options2 = {
                            todayBtn: "linked",
                            orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
                            startDate: new Date()
                        };
                        $('#end_data').datepicker(options2);
                        $('#end_time').timepicker({
                            showMeridian:false
                        });
                    });
                </script>
                <div class="col-sm-9">
                    <div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
                        <div style="width: 300px">
                            <input value="{$data['end_data']}" required="required" type="text" class="input-sm form-control" id="end_data" name="end_time[data]" placeholder="结束日期" style="width: 150px"/>
                            <input value="{$data['end_time']}" required="required" type="text" class="input-sm form-control" id="end_time" name="end_time[time]" style="width: 150px"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">报名截止</label>
                <script>
                    init.push(function () {
                        var options3 = {
                            todayBtn: "linked",
                            orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
                            defaultDate: '+1d',
                            startDate: new Date()
                        };
                        $('#cut_off_data').datepicker(options3);
                        $('#cut_off_time').timepicker({
                            showMeridian:false
                        });
                    });
                </script>
                <div class="col-sm-9">
                    <div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
                        <div style="width: 300px">
                            <input  value="{$data['cut_off_data']}" type="text" class="input-sm form-control _datepicker-contorl" id="cut_off_data" name="cut_off_time[data]"   placeholder="截止日期" style="width: 150px" required="1" />
                            <input  value="{$data['cut_off_time']}" type="text" id="cut_off_time" class="input-sm form-control" name="cut_off_time[time]"  style="width: 150px" />
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-sm-2" for="address">活动地点</label>
                <div class="col-sm-9">
                    <input value="{$data['address']}" type="text" class="form-control form-small" id="address" name="address" maxlength="100" />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2" for="content">活动内容</label>
                <div class="col-sm-9">
                    {$ueditor_output}
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-6">
                    <button id="push" type="submit" class="btn btn-primary">发布</button>
                    &nbsp;&nbsp;
                    <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>

{include file="$tpl_dir_base/footer.tpl"}
