<!-- 用户详细信息弹出框 -->
{literal}
    <script type="text/html" id="tpl_member_detail" xmlns="http://www.w3.org/1999/html">
        <input type="hidden" name="member_detail_uid" value="<%=data.uid%>">
        <input type="hidden" name="member_detail_active" value="<%=data.active%>">
        <div class="profile clearfix">
            <% if(data.face){%>
            <div class="img-box pull-left panel-padding">
                <img src="<%=data.face%>" alt="" class="">
            </div>
            <%}%>
            <div class="profile-name">
                <h4 class="col-md-6 text-left"><%=data.username%></h4>

                <span><%=data._qywxstatus%></span>
            </div>
        </div>
        <div class="cleafix"></div>
        <dl class="dl-horizontal padding-sm">
            <dt>性别</dt>
            <dd class="text-left"><%=data._gender%></dd>
            <dt class="text-left">手机号</dt>
            <dd class="text-left"><%=data.mobilephone%></dd>
            <dt class="text-left">微信号</dt>
            <dd class="text-left"><%=data.weixin%></dd>
            <dt class="text-left">电子邮箱</dt>
            <dd class="text-left"><%=data.email%></dd>
			<dt class="text-left">职位</dt>
			<dd class="text-left"><%=data.job%></dd>
			<dt class="text-left">所在部门</dt>
			<%for (var i in data.cd_names) {%>
			<dd class="text-left"><%=data.cd_names[i].cd_name%><% if (data.cd_names[i].mp_name) {%>:<%=data.cd_names[i].mp_name%><%}%></dd>
			<%}%>
            <%jQuery.each(data.fields, function(key, item) {%>
				<% if (item.value && item.value != '0000-00-00') {%>
				<dt class="text-left"><%=item.desc%></dt>
				<dd><%=item.value%></dd>
				<%}%>
            <%});%>
        </dl>
    </script>

    <script type="text/html" id="tpl_member_list">
        <%if (!jQuery.isEmptyObject(list)) {%>
            <%jQuery.each(list, function(key, item) {%>
            <tr class="row_member" data-id="<%=item.m_uid%>" id="row_member_<%=item.m_uid%>">
                <td class="text-left">
                    <label class="px-single">
                        <input type="checkbox" name="delete" class="px" value="<%=item.m_uid%>" data-name="<%=item.m_username%>">
                        <span class="lbl"></span>
                    </label>
                </td>
                <td class="text-left td_name">
                    <%if (item.m_face) {%>
                    <img src="<%=item.m_face%>" alt="" class="">
                    <%}%>
                    <%=item.m_username.substring(0,12)%>
                </td>
                <td><%=item.m_gender%></td>
                <td><%=item.job%></td>
                <td><%=item.m_mobilephone%></td>
                <td><%=item.m_email%></td>
                <td>
                    <%if (item.m_qywxstatus == 4) {%>
                    未关注
                    <%}else if (item.m_qywxstatus == 1) {%>
                    已关注
                    <%}else {%>
                    已冻结
                    <%}%>
                </td>
            </tr>
            <%});%>
        <%}else{%>
            <tr>
                <td colspan="7" class="warning">暂无对应用户数据</td>
            </tr>
        <%}%>
    </script>

    <script type="text/html" id="tpl_member_edit">
        <tr>
            <td  class="text-right"><span class="text-danger">*</span>姓名</td>
            <td class="text-left">
                <div class="form-inline">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="请输入" name="username" value="<% if (data && data.username) {%><%=data.username%><%} %>">
                    </div>
                </div>
            </td>
        </tr>
		<%if (!(data && data.username)) {%>
		<tr>
            <td  class="text-right">账号</td>
            <td class="text-left">
                <div class="form-inline">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="用户唯一标识, 不可更改, 不支持中文" name="openid" value="" />
                    </div>
                </div>
            </td>
        </tr>
		<%}%>
        <tr>
            <td class=" panel-padding-h text-left" colspan="2"><span class="text-danger">*</span>选择所在部门</td>
        </tr>
        <%if (data && data.cd_ids && 0 < data.cd_ids.length) {%>
            <% for(var i in data.cd_ids) {%>
                <tr class="member_tr_department">
                    <td><button class="btn btn-danger _delete" type="button" style="display: none;">删除</button></td>
                    <td class="text-left">
                        <div class="select2-info">
                            <div class="department_select_layer" data-name="cd_ids[]" data-value="<%=i%>" style="width:300px;">
                            </div>
                        </div>
                        <div>
                            <select name="mp_ids[]" class="_select_positions form-control form-group-margin">
                                <% for(var j in positions) {%>
                                <option value="<%=j%>" <%if (j == data.cd_ids[i]) {%>selected="selected"<%}%>><%=positions[j]%></option>
                                <%}%>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr class="member_tr_hr">
                    <td colspan="2"> <hr></td>
                </tr>
            <%}%>
        <%}else {%>
        <tr class="member_tr_department">
            <td><button class="btn btn-danger _delete" type="button" style="display: none;">删除</button></td>
            <td class="text-left">
                <div class="select2-info form-group-margin">
                    <div class="department_select_layer" data-name="cd_ids[]" data-value="<%=jQuery('#current_department_id').val()%>" style="width:300px;">
                    </div>
                </div>
                <div>
                    <select name="mp_ids[]" class="_select_positions form-control form-group-margin">
                        <% for(var i in positions) {%>
                        <option value="<%=i%>"><%=positions[i]%></option>
                        <%}%>
                    </select>
                </div>

            </td>
        </tr>
        <tr class="member_tr_hr">
            <td colspan="2"> <hr></td>
        </tr>
        <%}%>
        <tr>
            <td class="text-right" colspan="2"><button class="btn btn-primary" name="btn_member_add_department" type="button">添加所在部门</button></td>
        </tr>
        <tr>
            <td class="text-right">性别</td>
            <td class="text-left">

                <label class="radio-inline">
                    <input type="radio" name="gender" value="1" <%if (data && data.gender == 1) {%>checked="checked"<%} %>>
                    <span class="lbl">男</span>

                </label>
                <label class="radio-inline">
                    <input type="radio" name="gender" value="2" <% if(data && data.gender == 2) {%>checked="checked"<%} %>>
                    <span class="lbl">女</span>
                </label>

            </td>
        </tr>
        <tr>
            <td colspan="2"> <hr></td>
        </tr>
        <tr>
            <td class=" panel-padding-h text-left" colspan="2"><span class="text-danger">*</span>身份验证信息(以下三种信息不可同时为空)</td>
        </tr>
        <tr>
            <td class="text-right">手机号</td>
            <td class="text-left">
                <input type="text" class="form-control" maxlength="11" placeholder="手机号" name="mobilephone" value="<% if (data && data.mobilephone) {%><%=data.mobilephone%><%} %>"></td>
        </tr>
        <tr>
            <td class="text-right">微信号</td>
            <td class="text-left" style="border:0px;">
                <input type="text" class="form-control" placeholder="微信号" maxlength="30" name="weixin" value="<% if (data && data.weixin) {%><%=data.weixin%><%} %>"></td>
        </tr>
        <tr>
            <td class="text-right">电子邮箱</td>
            <td class="text-left" style="border:0px;">
                <input type="text" class="form-control" placeholder="电子邮箱" maxlength="50" name="email" value="<% if (data && data.email) {%><%=data.email%><%} %>"></td>
        </tr>
        <tr>
            <td colspan="2"> <hr></td>
        </tr>
        <tr>
            <td  class="text-right">职位</td>
            <td>
                <input type="text" class="form-control" placeholder="该员工的职位" name="job" value="<% if (data && data.job) {%><%=data.job%><%} %>">
            </td>
        </tr>
        <% jQuery.each(fields, function(k, v){ %>

        <tr>
            <td class="text-right"><%=v.desc%></td>
            <td>
                <% if (v.key == 'birthday') {%>
                    <span style="position: relative; z-index: 9999;">
                <%}%>
                    <input type="text" maxlength="<% if (v.key == 'idcard') {%>18<% }else if (v.key == 'qq') {%>11<%} else {%>500<%}%>" class="form-control field_<%=v.key%>" placeholder="<%=v.desc%>" name="fields[<%=v.key%>]" value="<% if (data.fields && data.fields[v.key]) {%><%=data && data.fields[v.key].value%><%} %>">
                <% if (v.key == 'birthday') {%>
                    </span>
                <%}%>
            </td>
        </tr>
        <%});%>
        <tr>
            <td  class="text-right">排序号</td>
            <td>
                <input type="text" class="form-control" placeholder="该员工在部门内的排序，请填写数字" name="displayorder" value="<% if (data && data.displayorder) {%><%=data.displayorder%><%} %>">
            </td>
        </tr>
    </script>
{/literal}