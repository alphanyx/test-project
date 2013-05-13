<?php defined('SYSPATH') or die('No direct script access.');

class Multidomain_Route_Translate extends Multidomain_Abstract {

	private $defaultTranslationFile = 'route-translation';
	private $translations = array();
	private $projectSettings = array();

	public function __construct() {
		$this->projectSettings = Multidomain_Multidomainconfig::instance()->getProjectSettings();

		$this->loadTranslations();
	}


	public function decodeUri(Request &$request, $translateInto = 'en') {
		$uri = $request->uri();

		$uri = strtolower($uri);

		foreach ($this->translations as $lang => $translations) {
			foreach ($translations as $translated => $original) {
				if (strcasecmp($translated, $uri) == 0) {
					$uri = $original;
					break;
				}
			}
		}

		$request->uri($uri);
	}

	public function translateURI($uri, $params) {
		$translateInto = 'en';

		if (isset($this->projectSettings['routeTranslationLang'])) {
			$translateInto = $this->projectSettings['routeTranslationLang'];
		}
		if (isset($params['lang'])) {
			$translateInto = $params['lang'];
		}

		if ($translateInto != $defaultLanguage) {
			if (isset($this->translations[$translateInto]) && is_array($this->translations[$translateInto])) {
				foreach ($this->translations[$translateInto] as $translated => $original) {
					if (strcasecmp($original, $uri) == 0) {
						$uri = $translated;
					}
				}
			}
		}

		return $uri;
	}

	protected function loadTranslations() {


		$translationFile = $this->defaultTranslationFile;
		if (isset($this->projectSettings['routeTranslation'])) {
			$translationFile = $this->projectSettings['routeTranslation'];
		}
		$this->translations = Kohana::$config->load($translationFile)->as_array();
	}
}