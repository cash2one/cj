<?php

return array(

	'_ERR_EMPTY_POST_UID' => '190000:缺少人员ID提交',
	'_ERR_EMPTY_USER_DATA' => '190001:没有人员信息',
	'_ERR_EMPTY_CD_ID' => '190002:部门ID不能为空',
	'_ERR_EMPTY_DEP_DATA' => '190003:没有部门信息',
	'_ERR_VIEW_UID_CAN_NOT_ARRAY' => '190004:查看详情提交的ID不得为多个',
	'_ERR_EMPTY_AND_ISARRAY_UID' => '190005:人员ID不能为空并且不能为数组',
	'_ERR_EMPTY_ACTIVE_STATUS' => '190006:缺失启用或禁用状态值',
	'_ERR_IS_ACTIVE_NOW' => '190007:已经是启用状态',
	'_ERR_IS_UNACTIVE_NOW' => '190008:已经是禁用状态',
	'_ERR_EMPTY_MB_UID_CDID' => '190009:不能提交为空的权限范围',
	'_ERR_DELETE_IS_FIAL' => '190010:删除失败',
	'_ERR_CAN_NOT_CONNECT_QY' => '190011:连接微信企业号失败',
	'_ERR_DELETE_FAIL' => '190012:删除失败,失败人员名称({$username})',
	'_ERR_EDIT_FAIL' => '190013:编辑失败,失败人员名称({$username})',
	'_ERR_EMPTY_FIELD' => '190014:属性规则不得为空',
	'_ERR_FIELD_MISS' => '190015:缺失固定规则({$name})',
	'_ERR_FIELD_OUT_OF_RANGE' => '190016:提交的规则值超出合法范围({$name})',
	'_ERR_EMPTY_LAID' => '190017:标签ID不能为空',
	'_ERR_LABEL_CAN_NOT_REPEATED' => '190018:标签不能重复',
	'_ERR_MISS_REQUIRED_VALUE' => '190019:缺少必要提交值({$name})',
	'_ERR_MISS_FIELD' => '190020:丢失人员属性规则',
	'_ERR_SEARCH_PARAMS' => '190021:错误的搜索条件',

	'_ERR_IMPORT' => '190022:导入失败',
	'_ERR_PLEASE_RESTART' => '190023:请重试',
	'_ERR_MISS_PAGE' => '190024:丢失必要参数(page)',
	'_ERR_MISS_ACTION' => '190025:丢失必要参数(action)',
	'_ERR_WXQY_GET_MEMBER' => '190026:获取微信人员列表失败',
	'_ERR_WXQY_GET_USER_CACHE' => '190027:解析缓存失败',
	'_ERR_WXQY_UPDATE' => '190028:同步至微信端失败',
	'_ERR_NO_PEOPLE_TO_SYNCHRO' => '190029:没有人员可以同步',
	'_ERR_NO_DEP_TO_SYNCHRO' => '190029:没有部门可以同步',
	'_ERR_CUSTOM_NAME_IS_RECUR' => '190030:屬性字段名称不能重复:({$name})',
	'_ERR_MOBILE_WEIXINID_EMAIL_CANNOT_ALL_EMPTY' => '190031:手机号、微信号、邮箱不得同时为空',
	'_ERR_MUST_HAVE_DEP_NAME' => '190032:必须有姓名和部门',
	'_ERR_MOBILE_IS_RECUR' => '190033:手机号重复',
	'_ERR_WEIXINID' => '190034:微信号格式错误',
	'_ERR_MOBILE' => '190035:手机号格式错误',
	'_ERR_EMAIL' => '190036:邮箱格式错误',
	'_ERR_EMPTY_USER_DATA_OR_NO_PERMISSION' => '190022:没有人员信息获取无权查看',

	'_ERR_EMPTY_GET_CDID' => '190050:缺少部门ID',
	'_ERR_EMPTY_POST_CDNAME' => '190051:缺少参数部门名称',
	'_ERR_EMPTY_POST_PERMISSION_DEPARTMENT' => '190052:请勾选指定部门',
	'_ERR_NOT_EXISTS_MEMBER' => '190053:人员不存在',
	'_ERR_NOT_EXISTS_DEPARTMENT' => '190054:部门信息不存在',
	'_ERR_EMPTY_POST_LNAME' => '190055:标签名不能为空',
	'_ERR_EXISTS_LABEL_NAME' => '190056:标签名已存在',
	'_ERR_EMPTY_GET_LAID' => '190057:缺少参数标签ID',
	'_ERR_NOT_EXISTS_LABEL' => '190058:标签信息不存在',
	'_ERR_EMPTY_POST_MUID' => '190059:请勾选要添加的人',
	'_ERR_EXISTS_MEMBER_LABEL' => '190060:人员已经在标签里',
	'_ERR_EMPTY_DEPARTMENT_DELETE' => '190060:请指定要删除的部门',
	'_ERR_DEPARTMENT_HAVE_DELETED' => '190061:部门不存在或已被删除',
	'_ERR_DEPARTMENT_NOT_EMPTY' => '190062:指定待删除的部门下存在成员，请先移除该部门的成员后再删除',
	'_ERR_CDNAME_LENGTH_OVER_MAX' => '190064:部门名称长度不合法',
	'_ERR_CDNAME_NOT_SPECIAL_STR' => '190065:部门名称不能包含特殊字符',
	'_ERR_CDNAME_EXISTS' => '190066:部门名称已被使用',
	'_ERR_NOT_EMPTY_CHILD_DEPARTMENT' => '190067:不能删除有子部门的部门',
	'_ERR_EMPTY_DUMP_DATA' => '190068:没有待导入的数据',
	'_ERR_NAME_DATA_NORMAL' => '190069:名称数据异常',
	'_ERR_UPLOAD_FAILED' => '190070:上传失败',
	'_ERR_NO_DEPARTMENT_MATCH' => '190071:没有找到对应的部门',
	'_ERR_EMPTY_USERNAME' => '19072:用户姓名不能为空',
	'_ERR_EMPTY_WX_MOBILE_EMAIL' => '190073:微信号手机号邮箱不能同时为空',
	'_ERR_MEMBER_INFO_EXISTS' => '190074:人员信息冲突',
	'_ERR_NOT_TRUE_FORMAT' => '190075:上传列表格式不正确',
	'_ERR_FAILED_UPDATE' => '190076:同步微信失败',
	'_ERR_EMPTY_UPID' => '190077:缺少参数级部门id',
	'_ERR_DELETE_DEPARTMENT' => '190078:删除部门失败',
	'_ERR_PARSE_FAILED' => '190079:解析文件失败',
	'_ERR_NOT_OPERATE_TOPID' => '190080:顶级部门不能操作',
	'_ERR_LABEL_OVER_LENGTH' => '190081:标签名超出长度',
	'_ERR_ADD_EMAIL_BEFORE_INVITE' => '190082:请先填写邮箱再发送邀请',
	'_ERR_USER_DEPARTMENT_ERROR' => '190083:用户部门数据异常'

);
