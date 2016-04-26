<?php
/**
 * 统计各种操作, 在需要统计的地方调用: startup_env::set
 *
 * $Author$
 * $Id$
 */

class profiler {

	protected $_available_sections = array(
		'custom',
		'benchmark',
		'uri_string',
		'get',
		'post',
		'memory_usage',
		'http_headers',
		'mysql',
		'cmem'
	);

	protected $compiles = array();

	public function __construct($config = array()) {

		if (!$config || !is_array($config)) {
			$config = $this->_available_sections;
		}

		$this->set_section($config);
	}

	public function set_section($config) {

		foreach ($config as $section) {

			if (in_array($section, $this->_available_sections)) {
				$this->compiles[] = $section;
			}
		}

	}

	protected function _compile_benchmark() {
		$benchmark = benchmark::get_instance();
		$output = '';
		foreach ($benchmark->maker as $key => $val) {
			$profile[$key] = $benchmark->elapsed_time($key);
		}

		if (!$profile) {
			return $output;
		}

		$output .= '<fieldset id="profiler_benchmarks" style="border:1px solid #900;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#900;">&nbsp;&nbsp; benchmarks &nbsp;&nbsp;</legend>';
		$output .= "<table style='width:100%'>";

		foreach ($profile as $key => $val) {
			if (!$key) {
				continue;
			}

			$key = ucwords(str_replace(array('_', '-'), ' ', $key));
			$output .= "<tr><td style='padding:5px;width:50%;color:#000;font-weight:bold;background-color:#ddd;'>".$key."&nbsp;&nbsp;</td><td style='padding:5px;width:50%;color:#900;font-weight:normal;background-color:#ddd;'>".$val."s</td></tr>";
		}

		$output .= "</table>";
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_get() {
		$output = '';
		$output .= '<fieldset id="profiler_get" style="border:1px solid #cd6e00;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#cd6e00;">&nbsp;&nbsp; $_GET &nbsp;&nbsp;</legend>';

		if (count($_GET) == 0) {
			$output .= "<div style='color:#cd6e00;font-weight:normal;padding:4px 0 4px 0'>No GET Data </div>";
		} else {
			$output .= "<table style='width:100%; border:none'>";

			foreach ($_GET as $key => $val) {
				if (! is_numeric($key)) {
					$key = "'".$key."'";
				}

				$output .= "<tr><td style='width:50%;color:#000;background-color:#ddd;padding:5px'>&#36;_GET[".$key."]&nbsp;&nbsp; </td><td style='width:50%;padding:5px;color:#cd6e00;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val)) {
					$output .= "<pre>".htmlspecialchars(stripslashes(print_r($val, true)))."</pre>";
				} else {
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>";
			}

			$output .= "</table>";
		}

		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_post() {
		$output = '';
		$output .= '<fieldset id="profiler_post" style="border:1px solid #009900;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#009900;">&nbsp;&nbsp; $_POST &nbsp;&nbsp;</legend>';

		if (count($_POST) == 0) {
			$output .= "<div style='color:#009900;font-weight:normal;padding:4px 0 4px 0'>No Post Data </div>";
		} else {
			$output .= "<table style='width:100%'>";

			foreach ($_POST as $key => $val) {
				if (! is_numeric($key)) {
					$key = "'".$key."'";
				}

				$output .= "<tr><td style='width:50%;padding:5px;color:#000;background-color:#ddd;'>&#36;_POST[".$key."]&nbsp;&nbsp; </td><td style='width:50%;padding:5px;color:#009900;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val)) {
					$output .= "<pre>".htmlspecialchars(stripslashes(print_r($val, TRUE)))."</pre>";
				} else {
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>";
			}

			$output .= "</table>";
		}
		$output .= "</fieldset>";

		return $output;

	}

	protected function _compile_uri_string() {
		$output = '';
		$output .= '<fieldset id="profiler_uri_string" style="border:1px solid #000;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#000;">&nbsp;&nbsp; URI String &nbsp;&nbsp;</legend>';

		if (empty($_SERVER['SCRIPT_URL'])) {
			$output .= "<div style='color:#000;font-weight:normal;padding:4px 0 4px 0'>No URI String </div>";
		} else {
			$output .= "<div style='color:#000;font-weight:normal;padding:4px 0 4px 0'>".$_SERVER['SCRIPT_URL']."</div>";
		}

		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_memory_usage() {
		$output = '';
		$output .= '<fieldset id="profiler_memory_usage" style="border:1px solid #5a0099;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#5a0099;">&nbsp;&nbsp; Memory Usage &nbsp;&nbsp;</legend>';

		if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
			$mem = '';
			if ($usage < 1024) {
				$mem = $usage." bytes";
			} elseif ($usage < 1048576) {
				$mem = round($usage / 1024,2)." K";
			} else {
				$mem = round($usage / 1048576,2)." M";
			}

			$output .= "<div style='color:#5a0099;font-weight:normal;padding:4px 0 4px 0'>".$mem.' </div>';
		} else {
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:4px 0 4px 0'> No memory_usage </div>";
		}

		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_http_headers() {
		$output = '';
		$output .= '<fieldset id="profiler_http_headers" style="border:1px solid #000;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#000;">&nbsp;&nbsp; Headers &nbsp;&nbsp;</legend>';

		$output .= "<table style='width:100%'>";

		foreach(array('HTTP_ACCEPT', 'HTTP_USER_AGENT', 'HTTP_CONNECTION', 'SERVER_PORT', 'SERVER_NAME', 'REMOTE_ADDR', 'SERVER_SOFTWARE', 'HTTP_ACCEPT_LANGUAGE', 'SCRIPT_NAME', 'REQUEST_METHOD',' HTTP_HOST', 'REMOTE_HOST', 'CONTENT_TYPE', 'SERVER_PROTOCOL', 'QUERY_STRING', 'HTTP_ACCEPT_ENCODING', 'HTTP_X_FORWARDED_FOR') as $header) {

			$val = '';
			if (isset($_SERVER[$header])) {
				$val = $_SERVER[$header];
			}

			$output .= "<tr><td style='vertical-align: top;width:50%;padding:5px;color:#900;background-color:#ddd;'>".$header."&nbsp;&nbsp;</td><td style='width:50%;padding:5px;color:#000;background-color:#ddd;'>".$val."</td></tr>";
		}

		$output .= "</table>";
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_mysql() {

		$db = startup_env::get('profile_db');
		$output = '';
		if (!$db) {
			return $output;
		}

		$total = 0;
		foreach ($db as $type => $ops) {
			$output .= '<fieldset style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
			$output .= '<legend style="color:#0000FF;">&nbsp;&nbsp;DATABASE:&nbsp; '.strtoupper($type) .'&nbsp;&nbsp;&nbsp;</legend>';
			$output .= "<table style='width:100%;'>";

			foreach ($ops as $op => $val) {
				$op = strtoupper($op);

				if ($type == 'sql') {
					$output .= "<tr><td style='padding:5px; vertical-align: top;width:1%;color:#900;font-weight:normal;background-color:#ddd;'>".$val[0]."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;font-weight:normal;background-color:#ddd;'>".$val[1]."</td></tr>";
				} else {
					$output .= "<tr><td style='padding:5px; vertical-align: top;width:1%;color:#900;font-weight:normal;background-color:#ddd;'>".$op."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>";
				}

				if ($type == 'optype') {
					$total += $val;
				}
			}

			$output .= "</table>";
			$output .= "</fieldset>";
		}

		$output .= '<fieldset style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#0000FF;">&nbsp;&nbsp;DATABASE:&nbsp; MySQL TOTAL Query&nbsp;&nbsp;&nbsp;</legend>';
		$output .= "<table style='width:100%;'>";

		$output .= "<tr><td style='padding:5px; vertical-align: top;width:1%;color:#900;font-weight:normal;background-color:#ddd;'>".'TOTAL'."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;font-weight:normal;background-color:#ddd;'>".$total."</td></tr>";

		$output .= "</table>";
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_custom() {
		$output = '';
		$custom = startup_env::get('profile_custom');

		$output .= '<fieldset id="profiler_custom" style="border:1px solid #cd6e00;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#cd6e00;">&nbsp;&nbsp; CUSTOM &nbsp;&nbsp;</legend>';

		if (!is_array($custom) || count($custom) == 0) {
			$output .= "<div style='color:#cd6e00;font-weight:normal;padding:4px 0 4px 0'>No CUSTOM Data </div>";
		} else {
			$output .= "<table style='width:100%; border:none'>";

			foreach ($custom as $key => $val) {
				$output .= "<tr><td style='width:50%;color:#000;background-color:#ddd;padding:5px'>".$key."&nbsp;&nbsp; </td><td style='width:50%;padding:5px;color:#cd6e00;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val)) {
					$output .= "<pre>".htmlspecialchars(stripslashes(print_r($val, true)))."</pre>";
				} else {
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>";
			}

			$output .= "</table>";
		}

		$output .= "</fieldset>";

		return $output;
	}

	public function run() {

		$display = false;
		$output = "<div id='profiler' style='clear:both;background-color:#fff;padding:10px;'>";

		foreach ($this->compiles as $section) {
			$func = "_compile_".$section;
			$output .= $this->$func();
			$display = true;
		}

		if (!$display) {
			$output .= '<p style="border:1px solid #5a0099;padding:10px;margin:20px 0;background-color:#eee"> profiler_no_profiles </p>';
		}

		$output .= "</div>";

		return $output;
	}

	public static function set_custom($key, $value) {

		if (startup_env::get('profiler')) {
			$custom = startup_env::get('profile_custom');

			$custom[$key] = $value;

			startup_env::set('profile_custom', $custom);
		}
	}

	protected function _compile_cmem() {
		$output = '';
		$cmem = startup_env::get('profile_cmem');

		if (!$cmem) {
			return $output;
		}

		$total = 0;
		foreach ($cmem as $table => $ops) {
			$output .= '<fieldset style="border:1px solid #995300;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
			$output .= '<legend style="color:#995300;">&nbsp;&nbsp;CMEM:&nbsp; '.$table .'&nbsp;&nbsp;&nbsp;</legend>';
			$output .= "<table style='width:100%;'>";

			foreach ($ops as $op => $val) {
				$op = strtoupper($op);
				$output .= "<tr><td style='padding:5px; vertical-align: top;width:1%;color:#900;font-weight:normal;background-color:#ddd;'>".$op."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>";

				if ($op != 'CONFIG') {
					$total += $val;
				}
			}

			$output .= "</table>";
			$output .= "</fieldset>";
		}

		/** total CMEM */
		$output .= '<fieldset style="border:1px solid #995300;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= '<legend style="color:#995300;">&nbsp;&nbsp;CMEM:&nbsp; TOTAL &nbsp;&nbsp;&nbsp;</legend>';
		$output .= "<table style='width:100%;'>";

		$output .= "<tr><td style='padding:5px; vertical-align: top;width:1%;color:#900;font-weight:normal;background-color:#ddd;'>".'TOTAL'."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;font-weight:normal;background-color:#ddd;'>".$total."</td></tr>";

		$output .= "</table>";
		$output .= "</fieldset>";


		return $output;
	}
}
