<?php
/**
 * place.php
 * 错误代码库/场所相关
 * 3xxx
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_oa_common_place {

	const VALIDATOR_PLACE_NAME_STRING_ERROR = '3000:场地名称不能包含特殊字符';
	const VALIDATOR_PLACE_NAME_STRING_SLASHES = '30001:场地名称禁止包含特殊字符';
	const VALIDATOR_PLACE_NAME_LENGTH_MAX_ERROR = '3002:场地名称长度不能超过 %s 个字符';
	const VALIDATOR_PLACE_NAME_LENGTH_RANGE_ERROR = '3003:场地名称长度应该介于 %s 到 %s 个字符之间';
	const VALIDATOR_PLACE_ADDRESS_STRING_ERROR = '3004:场地详细地址不能包含特殊字符';
	const VALIDATOR_PLACE_ADDRESS_STRING_SLASHES = '3005:场地详细地址禁止包含特殊字符';
	const VALIDATOR_PLACE_ADDRESS_LENGTH_RANGE_ERROR = '3006:场地详细地址长度应该介于 %s 到 %s 个字符之间';
	const VALIDATOR_PLACE_ADDRESS_LENGTH_MAX_ERROR = '3007:场地详细地址长度不能超过 %s 个字符';
	const VALIDATOR_MEMBER_LEVEL_ERROR = '3008:指定权限级别设置错误 %s';

	const VALIDATOR_PLACE_REGION_NAME_STRING_ERROR = '3009:分区名称不能包含特殊字符';
	const VALIDATOR_PLACE_REGION_NAME_STRING_SLASHES = '3010:分区名称禁止包含特殊字符';
	const VALIDATOR_PLACE_REGION_NAME_LENGTH_MAX_ERROR = '3011:分区名称长度不能超过 %s 个字符';
	const VALIDATOR_PLACE_REGION_NAME_LENGTH_RANGE_ERROR = '3012:分区名称长度应该介于 %s 到 %s 个字符之间';

	const VALIDATOR_PLACE_TYPE_NAME_STRING_ERROR = '3013:类型名称不能包含特殊字符';
	const VALIDATOR_PLACE_TYPE_NAME_STRING_SLASHES = '3014:类型名称禁止包含特殊字符';
	const VALIDATOR_PLACE_TYPE_NAME_LENGTH_MAX_ERROR = '3015:类型名称长度不能超过 %s 个字符';
	const VALIDATOR_PLACE_TYPE_NAME_LENGTH_RANGE_ERROR = '3016:类型名称长度应该介于 %s 到 %s 个字符之间';

	const VALIDATOR_PLACE_LEVEL_NAME_STRING_ERROR = '3017:权限级别称谓不能包含特殊字符';
	const VALIDATOR_PLACE_LEVEL_NAME_STRING_SLASHES = '3018:权限级别称谓禁止包含特殊字符';
	const VALIDATOR_PLACE_LEVEL_NAME_LENGTH_MAX_ERROR = '3019:权限级别称谓长度不能超过 %s 个字符';
	const VALIDATOR_PLACE_LEVEL_NAME_LENGTH_RANGE_ERROR = '3020:权限级别称谓长度应该介于 %s 到 %s 个字符之间';

	const PLACE_TYPE_ADD_LEVEL_NOT_DEFINED = '3021:权限级别称谓未定义或定义的类型错误: %s';
	const PLACE_TYPE_ADD_DB_ERROR = '3022:新增类型发生数据错误';

	const PLACE_TYPE_EDIT_NOT_EXISTS = '3023:待更新的类型不存在: %s';
	const PLACE_TYPE_EDIT_LEVEL_NOT_DEFINED = '3024:权限级别称谓未定义或者定义的类型错误: %s';
	const PLACE_TYPE_EDIT_DB_ERROR = '3025:更新类型发生数据错误';

	const TYPE_DELETE_REGION_NOT_EMPTY = '3026:类型内的分区不为空禁止删除，请先删除其下的分区后再试';
	const TYPE_DELETE_ID_NULL = '3027:必须指定待删除的类型';
	const TYPE_DELETE_DB_ERROR = '3028:删除类型发生数据错误';

	const TYPE_ADD_MAX_COUNT_LIMIT = '3029:系统限制只允许设置 %s 个类型，目前已达到极限';
	const TYPE_ADD_NAME_EXISTS = '3030:类型名称 %s 已存在，不允许重名';
	const TYPE_EDIT_NAME_EXISTS = '3031:类型名称 %s 已存在，不允许重名';

	const REGION_ADD_TYPE_NOT_EXISTS = '3032:指定类型不存在(ID=%s)';
	const REGION_ADD_PARENTID_NOT_EXISTS = '3033:上级分区不存在（%s）';
	const REGION_ADD_NAME_EXISTS = '3034:所选的分区下已存在同名分区（%）不能重复添加';
	const REGION_ADD_DB_ERROR = '3035:新建分区发生数据错误';
	const REGION_ADD_DEEPIN_TOO_DEEP = '3036:系统最多允许创建 %s 级分区，目前已达到最多分级';
	const REGION_ADD_PARENT_TYPE_ERROR = '3037:所选的上级分区与类型不匹配（regionid: %s, typeid: %s）';

	const REGION_EDIT_NOT_EXISTS = '3038:待编辑的分区不存在（ID=%s）';
	const REGION_EDIT_TYPE_MODIFY_CHILDREN_NOT_NULL = '3039:更改分区类型当前分区不能存在下级分区或者场所';

	const PLACE_TYPE_EDIT_NO_CHANGE = '3040:类型信息未改变不需要更新';

	const TYPE_NAME_DUPLICATE = '3041:已存在同名（%s）类型名，禁止重名';
	const TYPE_AMOUNT_TOO_MUCH = '3042:类型总数已超出系统限制（%s），禁止新增类型';
	const REGION_NAME_DUPLICATE = '3043:分区名称（%s）已被使用，禁止重名';

	const TYPE_EDIT_ID_ERROR = '3044:待编辑的类型ID错误';
	const TYPE_DELETE_ID_NULL_RESET = '3044:待删除的类型ID未指定';
	const TYPE_ID_ERROR = '3045:场所类型指定错误（ID=%s）';
	const REGION_ID_ERROR = '3046:分区指定错误（ID=%s）';
	const PLACE_ID_ERROR = '3047:场所指定错误（ID=%s）';

	const REGION_ADD_PARENT_ERROR = '3038:上级分区或者类型设置错误';

	const REGION_DELETE_HAVE_SUBREGION = '3039:(ID: %s)下级分区不为空，禁止删除';
	const REGION_DELETE_HAVE_PLACE = '3040:分区(ID: %s)内存在场所地点，禁止删除';
	const REGION_EDIT_NO_CHANGED = '3041:分区信息未发生改变不需要提交更新';

	const REGION_LIST_TYPE_NOT_EXISTS = '3042:所在类型（ID:%s）不存在';
	const REGION_LIST_PARENT_NOT_EXISTS = '3043:上级分区（ID:%s）不存在';

	const PLACE_VALIDATOR_PLACETYPEID = '3044:所在类型（ID:%s）不存在';
	const PLACE_VALIDATOR_PLACETYPEID_BY_REGION = '3055:所在类型（ID:%s）不存在';
	const PLACE_REGION_NULL = '3056:必须选择一个分区';
	const PLACE_TYPEID_NOT_EXISTS = '3057:场地类型（ID:%s）不存在';
	const PLACE_REGION_NOT_EXISTS = '3058:所在分区设置错误（ID:%s）';
	const PLACE_ADD_TYPE_ERROR = '3059:场所所在类型错误（ID:%s）';
	const PLACE_TYPE_NOT_EXISTS = '3060:所在类型设置错误（ID:%s）';
	const PLACE_MASTER_COUNT_ERROR_MAX = '3061:负责人最多只允许设置 %s 个';
	const PLACE_MASTER_COUNT_ERROR_MIN = '3062:负责人最少需要设置 %s 个';
	const PLACE_NORMAL_COUNT_ERROR_MAX = '3061:相关人员最多只允许设置 %s 个';
	const PLACE_NORMAL_COUNT_ERROR_MIN = '3062:相关人员最少需要设置 %s 个';

	const PLACE_ADD_DB_ERROR = '3063:新增地点发生数据错误';
	const PARASE_UPDATED_ERROR = '3064:获取发生改变的数据发生错误';

	const PLACE_DEEPIN_ERROR = '3065:场地所在分区必须为最后一级（%s/%s）';

	const PLACE_DELETE_UNKNOW = '3066:未指定要删除的场所地点ID';
	const PLACE_DELETE_FORMAT_ERROR = '3067:待删除的场所地点ID应该为数组列表';
	const PLACE_DELETE_NULL = '3068:待删除的场所地点不能为空';

	const TYPE_NOT_EXISTS = '3069:场所类型不存在（ID: %s）';
	const REGION_DEEPIN_ERROR = '3070:区域深度设置错误（%s）';
	const PARENTID_NOT_EXISTS = '3071:上级分区不存在（%s）';

	const UNKNOW_MEMBER_TYPE = '3072:未知的人员类型（%s）';
	const LOSE_MEMBER_PARAM = '3073:缺少参数（%s）';
	const UNKNOW_MEMBER_LEVEL = '3073:未知的权限级别（%s）';

	const MEMBER_MASTER_REGION_NONE = '3074:指定的分区不存在（placeregionid: %s）';
	const MEMBER_MASTER_PLACE_NONE = '3075:指定的地点不存在（placeid: %s）';
	const MEMBER_NORMAL_PLACE_NONE = '3076:指定的地点不存在（placeid: %s）';

	const PLACE_DUPLICATION = '3077:地点不能重复（%s）';

	const PLACEID_NOT_EXISTS = '3078:指定门店不存在或已被清理（ID: %s）';

	const NO_ID = '3079:类型和上级分区必须指定';

	const LOSE_REGION_GET_TYPEID = '3080:缺少 placetypeid 参数';
	const LOSE_REGION_GET_PARENTID = '3081:缺少 parentid 参数';
	const LOSE_REGION_GET_NAME = '3082:缺少 name 参数';
}
