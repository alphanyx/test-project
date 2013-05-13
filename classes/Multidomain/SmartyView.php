<?php

class Multidomain_SmartyView extends Kohana_SmartyView {

	public static function instance() {
		$_instance = parent::instance();

		$_instance->registerPlugin('function', 'multidomaininclude', 'smarty_function_multidomaininclude');

		return $_instance;
	}


	public function set_filename($file) {
		if (($path = Kohana::find_file('views', $file, Kohana::$config->load('smarty')->get('tpl_extension'))) === FALSE) {
			// check the alternative paths
			$path = $this->checkAlternativeFilePaths($file);
		}

		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

	protected function checkAlternativeFilePaths($file) {
		$origFile = $file;
		$multidomainPaths = Helper_Config::getSmartyIncludePaths();

		$request = Request::current();
		$file = trim(str_replace($request->domainTemplatepath(), '', $file), DIRECTORY_SEPARATOR);

		foreach ($multidomainPaths as $domainPath) {
			$tmpFile = trim($domainPath,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

			if (($path = Kohana::find_file('views', $tmpFile, Kohana::$config->load('smarty')->get('tpl_extension'))) !== FALSE) {
				return $path;
			}
		}

		// File not found, now throw the exception
		throw new View_Exception('The requested view :file could not be found', array(
			':file' => $origFile,
		));
	}

	public function render($file = NULL) {
		if (class_exists('Mobile_Detect')) {
			$mobiledetect = new Mobile_Detect();
			$this->_data['mobiledetect'] = $mobiledetect;
			$this->_data['is_mobile'] = $mobiledetect->isMobile();
			$this->_data['is_tablet'] = $mobiledetect->isTablet();
		}

		return parent::render($file);
	}
}