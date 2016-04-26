<?php
/**
 * view
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/view/smarty/Smarty.class.php';

class view {

	/**
	 * tpl
	 *
	 * @var mixed
	 */
	public  $tpl;

	/**
	 * _data
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * _constants
	 * 常量
	 *
	 * @var array
	 */
	protected $_constants;

	/**
	 * 创建一个Smarty的实例,并初使化模板目录信息.
	 *
	 * @return Smarty
	 */
	public function &get_smarty() {

		$smarty = new Smarty;

		$templates_key = startup_env::get('cfg_name').'.view.templates';
		$templates_ckey = startup_env::get('cfg_name').'.view.templates_c';

		$smarty->template_dir = config::get($templates_key);
		$smarty->compile_dir = config::get($templates_ckey);
		$smarty->addPluginsDir(dirname(__FILE__).'/view/smarty/plugins');

		/** 增加语言解析函数 */
		$smarty->registerPlugin('function', 'lang', 'parse_lang');

		/** 根据配置判断是否需要加载默认 modifiers */
		$need_default_modify_modules = config::get(startup_env::get('cfg_name').'.view.module_need_modify');
		if ($need_default_modify_modules && in_array(startup_env::get('module'), $need_default_modify_modules)) {
			//$smarty->default_modifiers = array('escape:"htmlall"');
		}

		/** 屏蔽 notice 错误 */
		error_reporting(E_ALL ^ E_NOTICE);
		return $smarty;
	}

	/**
	 * set
	 * 设置模板变量
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	public function set($key, $value) {

		$this->_data[$key] = $value;
	}

	public function get($key) {

		return empty($this->_data[$key]) ? null : $this->_data[$key];
	}

	/**
	 * set_class_constants
	 * 设置类常量
	 *
	 * @param  string $class
	 * @param  string $prefix
	 * @return true
	 */
	public function set_class_constants($class, $prefix = null) {

		if (!class_exists($class)) {
			return false;
		}

		$classObject = new ReflectionClass($class);
		$constants = $classObject->getConstants();
		if (!$constants) {
			return true;
		}

		if (!$prefix) {
			$parts = explode('_', $class);
			array_shift($parts);
			array_shift($parts);
			$prefix = strtoupper(join('_', $parts));
		}

		foreach ($constants as $key => $value) {
			$this->_constants['view']['const'][$prefix][$key] = $value;
		}

		return true;
	}

	/**
	 * 显示或者返回模板处理结果
	 *
	 * @param  mixed $tpl
	 * @param  bool  $return
	 * @return mixed
	 */
	public function render($tpl = null, $return = false) {

		$smarty =& $this->get_smarty();

		if ($this->_data) {
			foreach ($this->_data as $key => $value) {
				if ($value instanceof view) {
					$value = $value->render(null, true);
				}

				$smarty->assign($key, $value);
			}
		}

		if ($this->_constants) {
			$smarty->assign($this->_constants);
		}

		$tpl = ($tpl ? $tpl : $this->tpl).'.tpl';

		/** 将页面执行时间设置到模版中 */
		$cost = benchmark::elapsed_time('total_execution_time_start');
		$smarty->assign('__costtime', $cost);
		$content = $smarty->fetch($tpl);

		if ($return) {
			return $content;
		}

		return controller_response::get_instance()->append_body($content, 'view');
	}

}
