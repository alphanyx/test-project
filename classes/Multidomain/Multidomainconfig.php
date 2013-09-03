<?php defined('SYSPATH') or die('No direct script access.');

class Multidomain_Multidomainconfig extends Multidomain_Abstract {

	private static $config = null;
	private static $matches = array();

	private static $isInShell = false;

	public function getDomain() {
		$domain = '';
		if(php_sapi_name() == 'cli')
		{
			self::$isInShell = true;

			$domain = gethostname();
		} else {
			$domain = $_SERVER['HTTP_HOST'];
		}

		return $domain;
	}

	public function getCurrentProject() {
		$domain = $this->getDomain();

		if (isset(self::$matches[$domain])) {
			return self::$matches[$domain]->project;
		} else {
			$tmp = $this->matchDomain($domain);

			return $tmp->project;
		}
	}

	public function getCurrentEnvironment() {
		$domain = $this->getDomain();

		if (isset(self::$matches[$domain])) {
			return self::$matches[$domain]->environment;
		} else {
			$tmp = $this->matchDomain($domain);

			return $tmp->environment;
		}
	}

	public function matchDomain($domain = null) {
		if (!$domain) {
			$domain = $this->getDomain();
		}

		if (isset(self::$matches[$domain])) {
			return self::$matches[$domain];
		}

		$config = $this->getConfig();

		$info = array();
		if (!self::$isInShell) {
			if (isset($config['domains'][$domain])) {
				$info = $config['domains'][$domain];
			}
		} else {
			if (isset($config['hosts'][$domain])) {
				$info = $config['hosts'][$domain];
			}
		}
		if (is_array($info) && count($info)) {
			extract($info);

			return self::$matches[$domain] = (object)array(
				'project' => $project,
				'environment' => $environment,
				'templatepath' => $this->getTemplatePath($project, $environment),
			);
		} else {
			// default domain, if no multidomains are configured
			return (object)array(
				'project' => '',
				'environment' => '',
				'templatepath' => '',
			);
		}
	}

	protected function getGlobalSettings($projectname) {
		$config = $this->getConfig();

		$projects = $config['raw']['projects'];

		if (isset($projects[$projectname])) {
			if (isset($projects[$projectname]['settings']) && is_array($projects[$projectname]['settings'])) {
				return $projects[$projectname]['settings'];
			} else {
				return array();
			}
		} else {
			throw new Multidomain_Exception_Notfound('The Project with the name :project could not be found!', array(':project' => $projectname));
		}
	}

	public function getProjectSettings($projectname = null, $environment = null) {
		if (!$projectname) {
			$projectname = $this->getCurrentProject();
		}
		if ($environment === null) {
			$environment = $this->getCurrentEnvironment();
		}

		$config = $this->getConfig();

		$projects = $config['raw']['projects'];

		if (isset($projects[$projectname])) {
			$projectInfo = $projects[$projectname];

			$environmentSettings = array();
			$globalSettings = $this->getGlobalSettings($projectname);
			if ($environment !== false) {
				if (isset($projectInfo['environments'][$environment])) {
					$environmentInfo = $projectInfo['environments'][$environment];

					if (isset($environmentInfo['settings']) && is_array($environmentInfo['settings'])) {
						$environmentSettings = $environmentInfo['settings'];
					}
				} else {
					throw new Multidomain_Exception_Notfound('The Environment with the name :environment could not be found in the Project :project!', array(':environment' => $environment, ':project' => $projectname));
				}
			}

			return $environmentSettings = array_replace_recursive($globalSettings, $environmentSettings);
		} else {
			throw new Multidomain_Exception_Notfound('The Project with the name :project could not be found!', array(':project' => $projectname));
		}
	}

	public function getProjectEnvironment($projectname = null, $environment = null) {
		if (!$projectname) {
			$projectname = $this->getCurrentProject();
		}
		$config = $this->getConfig();

		$projects = $config['raw']['projects'];

		if (isset($projects[$projectname])) {
			$projectInfo = $projects[$projectname];

			if ($environment === null) {
				return $projectInfo;
			} else {
				if (isset($projectInfo['environments'][$environment])) {
					$environmentInfo = $projectInfo['environments'][$environment];

					return $environmentInfo;
				} else {
					throw new Multidomain_Exception_Notfound('The Environment with the name :environment could not be found in the Project :project!', array(':environment' => $environment, ':project' => $projectname));
				}
			}
		} else {
			throw new Multidomain_Exception_Notfound('The Project with the name :project could not be found!', array(':project' => $projectname));
		}
	}

	public function getDatabase($projectname, $environment) {
		if (!$projectname) {
			$projectname = $this->getCurrentProject();
		}
		if (!$environment) {
			$environment = $this->getCurrentEnvironment();
		}
		$environmentInfo = $this->getProjectEnvironment($projectname, $environment);

		if (isset($environmentInfo['database'])) {
			return $environmentInfo['database'];
		} else {
			return false;
		}
	}

	public function getTemplatePath($projectname = null, $environment = null) {
		if (!$projectname) {
			$projectname = $this->getCurrentProject();
		}
		if (!$environment) {
			$environment = $this->getCurrentEnvironment();
		}

		$projectSettings = $this->getProjectSettings($projectname, $environment);

		return isset($projectSettings['templates']) ? $projectSettings['templates'] : '';
	}

	public function getSmartyIncludesPath($projectname = null, $environment = null) {
		if (!$projectname) {
			$projectname = $this->getCurrentProject();
		}
		if (!$environment) {
			$environment = $this->getCurrentEnvironment();
		}

		$projectSettings = $this->getProjectSettings($projectname, $environment);

		$paths = array();

		if (isset($projectSettings['smartyIncludes'])) {
			if (is_array($projectSettings['smartyIncludes']) && count($projectSettings['smartyIncludes'])) {
				foreach ($projectSettings['smartyIncludes'] as $tmpPath) {
					$paths[] = $tmpPath;
				}
			} else {
				$paths[] = $projectSettings['smartyIncludes'];
			}
		}
		if (empty($paths)) {
			$paths[] = DIRECTORY_SEPARATOR;
		}

		return $paths;
	}

	protected function getConfig(){
		if (is_array(self::$config)) {
			return self::$config;
		}

		$rawConfig = Kohana::$config->load('multidomain')->as_array();

		$domains = $hosts = array();

		if (isset($rawConfig['projects']) && count($rawConfig['projects'])) {
			foreach ($rawConfig['projects'] as $projectname => $settings) {
				if (is_array($settings) && count($settings)) {

					if (is_array($settings['environments']) && count($settings['environments'])) {
						foreach ($settings['environments'] as $environment => $config) {
							$urls = $shellHosts = array();

							if (isset($config['domains'])) {
								if (is_array($config['domains']) && count($config['domains'])) {
									foreach ($config['domains'] as $a) {
										$urls[] = $a;
									}
								} else if (is_string($config['domains']) && !empty($config['domains'])) {
									$urls[] = $config['domains'];
								}
							}

							if (is_array($urls) && count($urls)) {
								$conf = array(
									'project' => $projectname,
									'environment' => $environment
								);

								foreach ($urls as $url) {
									if (isset($domains[$url]) && ($domains[$url]['project'] != $projectname || $domains[$url]['environment'] != $environment)) {
										if ($domains[$url]['project'] != $projectname) {
											throw new Multidomain_Exception_Multipleprojects('There are multiple Projects with the url ":host" configured!', array(':host' => $url));
										} else {
											throw new Multidomain_Exception_Multipleenvironments('There are multiple Project Environments with the url ":host" configured!', array(':host' => $url));
										}
									} else {
										$domains[$url] = $conf;
									}
								}
							}

							if (isset($config['shell'])) {
								if (is_array($config['shell']) && count($config['shell'])) {
									foreach ($config['shell'] as $a) {
										$shellHosts[] = $a;
									}
								} else if (is_string($config['shell']) && !empty($config['shell'])) {
									$shellHosts[] = $config['shell'];
								}
							}

							if (is_array($shellHosts) && count($shellHosts)) {
								$conf = array(
									'project' => $projectname,
									'environment' => $environment
								);

								foreach ($shellHosts as $host) {
									if (isset($domains[$host]) && ($domains[$host]['project'] != $projectname || $domains[$host]['environment'] != $environment)) {
										if ($domains[$host]['project'] != $projectname) {
											throw new Multidomain_Exception_Multipleprojects('There are multiple Projects with the host ":host" configured!', array(':host' => $host));
										} else {
											throw new Multidomain_Exception_Multipleenvironments('There are multiple Project Environments with the host ":host" configured!', array(':host' => $host));
										}
									} else {
										$hosts[$host] = $conf;
									}
								}
							}
						}
					}
				}
			}
		}

		return self::$config = array(
			'raw' => $rawConfig,
			'domains' => $domains,
			'hosts' => $hosts,
			'dummy' => array()
		);
	}
}