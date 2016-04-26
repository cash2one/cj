<?php

return array(

	// 微信端

	'_ERR_MISS_PARAMETER_TYPE' => '600100:缺少必要参数type',
	'_ERR_NO_DATA' => '600101:没有数据',
	'_ERR_MISS_PARAMETER_AFID' => '600102:缺少必要参数af_id',
	'_ERR_NO_AUTHORITY' => '600103:没有权限',
	'_ERR_IS_FIXED_NOTRUN' => '600104:固定流程没有转审批',
	'_ERR_IS_OPERATION' => '600105:已经操作过',
	'_ERR_IS_OVER' => '600106:已经结束',
	'_ERR_TRUN_APPROVER_ONLY_ONE' => '600107:转审批人只能有一人',
	'_ERR_IS_APPROVER_NOW' => '600108:已经是审批人',
	'_ERR_MISS_PARAMETER_RE_UID' => '600109:转审批人不能为空',
	'_ERR_MISS_MEMBER' => '600110:没有此人',
	'_ERR_MISS_PARAMETER_AFTID' => '600111:缺少必要参数aft_id',
	'_ERR_TEMP_CANT_USE' => '600112:没有模板使用权限',
	'_ERR_MISS_TEMP' => '600113:丢失模板数据',
	'_ERR_MISS_PARAMETER_TITLE' => '600114:缺少标题',
	'_ERR_MISS_PARAMETER_CONTENT' => '600115:缺少审批内容',
	'_ERR_MISS_MARK' => '600116:缺少备注',
	'_ERR_MISS_REQUIRED' => '600117:缺少必要自定义数据',
	'_ERR_TEMP_MISS_APPROVER_DATA' => '600118:固定模板审批人已不在企业号({$username})',
	'_ERR_RETURN_IS_PROMOTER' => '600119:转审批人不能是发起人',
	'_ERR_UNACTIVE' => '600120:还没有到达审批等级',
	'_ERR_COPY_CAN_NOT_HAVE_APPROVER_OR_MINE' => '600121:抄送人里不能有自己或者审批人',
	'_ERR_MISS_SUBJECT' => '600151:缺少参数af_subject',
	'_ERR_MISS_MESSAGE' => '600152:缺少参数af_message',
	'_ERR_MISS_SPLIST' => '600153:审批人不能为空',
	'_ERR_OVER_SPLIST' => '600154:审批人个数大于5个',
	'_ERR_RECUR_SPLIST' => '600155:审批人不能是自己',
	'_ERR_OVER_IMGCOUNT' => '600156:图片数量超过限制',
	'_ERR_NULL_OR_NO_PERMISSION' => '600157:该审批记录不存在或者没有权限操作该审批记录',
	'_ERR_NOT_CANCEL' => '600157：审批已经开始不能取消',
	'_ERR_MISS_CS' => '600158:抄送人不能为空',
	'_ERR_NOT_EXISTS' => '600159:该审批不存在或已被删除',
	'_ERR_SP_NOT_EXISTS' => '600160:审批人信息不存在',
	'_ERR_CS_NOT_EXISTS' => '600161:抄送人信息不存在',
	'_ERR_PRESS_TIME_MACH_MORE' => '600162:催办次数太过频繁',
	'_ERR_USER_IS_NOT_CS' => '600163:抄送人不能是自己',
	'_ERR_CS_IS_NOT_SP' => '600164:抄送人不能是审批人',
	'_ERR_SUBJECT_OVER_LENGTH' => '600165:审批主题超过15个字',


	// 后台
	'_ERR_CP_APPROVERS_CAN_NOT_NULL' => '600500:审批人不得为空',
	'_ERR_CP_TEMP_NAME_CAN_NOT_NULL' => '600501:流程名称不得为空',
	'_ERR_CP_APPROVERS_IS_REPEAT' => '600502:审批人({$appreover})重复',
	'_ERR_CP_APPROVER_IS_NOT_EXIST' => '600503:审批人不存在',
	'_ERR_CP_INSERT_ERROR' => '600504:操作入库失败',
	'_ERR_CP_PARAMS_CAN_NOT_EMPTY' => '600505:参数不得为空',
	'_ERR_CP_EMPTY_DATA' => '600506:没有可以导出的数据',
	'_ERR_CP_CUSTOM_NAME_CAN_NOT_RECUR' => '600507:自定义字段名称不得重复',

);
