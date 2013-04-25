<?php defined('SYSPATH') or die('No direct script access.');

class Multidomain_Request extends Kohana_Request {

	/**
	 * @var  string  request domain
	 */
	public static $domain;
	public static $domain_project;
	public static $domain_environment;
	public static $domain_templatepath;

	public static function factory($uri = TRUE, $client_params = array(), $allow_external = TRUE, $injected_routes = array()){
		Request::catch_project() ;

		return parent::factory($uri, $client_params, $allow_external, $injected_routes) ;
	}

	public static function catch_project($host = NULL) {
		if($host === NULL)
		{
			if(php_sapi_name() == 'cli' AND empty($_SERVER['REMOTE_ADDR']))
			{
				return FALSE;
			}

			$host = $_SERVER['HTTP_HOST'];
		}

		if(empty($host) OR Valid::ip($host))
		{
			return FALSE;
		}

		$results = Multidomain_Multidomainconfig::instance()->matchDomain($host);

		if ($results !== false) {
			self::$domain = $host;
			self::$domain_project = !empty($results->project) ? $results->project : false;
			self::$domain_environment = !empty($results->environment) ? $results->environment : false;
			self::$domain_templatepath = !empty($results->templatepath) ? $results->templatepath : false;
		}
	}

	/**
	 * Sets and gets the domain for the matched route.
	 *
	 * @param   string   $domain  Domain to execute the action
	 * @return  mixed
	 */
	public function domain($domain = NULL)
	{
		if ($domain === NULL)
		{
			// Act as a getter
			return self::$domain;
		}

		// Act as a setter
		self::$domain = (string) $domain;

		return $this;
	}

	/**
	 * Sets and gets the domain project for the matched route.
	 *
	 * @param   string   $domain  Domain project to execute the action
	 * @return  mixed
	 */
	public function domainProject($domainProject = NULL)
	{
		if ($domainProject === NULL)
		{
			// Act as a getter
			return self::$domain_project;
		}

		// Act as a setter
		self::$domain_project = (string) $domainProject;

		return $this;
	}

	/**
	 * Sets and gets the domain environment for the matched route.
	 *
	 * @param   string   $domain  Domain Environment to execute the action
	 * @return  mixed
	 */
	public function domainEnvironment($domainEnvironment = NULL)
	{
		if ($domainEnvironment === NULL)
		{
			// Act as a getter
			return self::$domain_environment;
		}

		// Act as a setter
		self::$domain_environment = (string) $domainEnvironment;

		return $this;
	}

	/**
	 * Sets and gets the domain environment for the matched route.
	 *
	 * @param   string   $domain  Domain Environment to execute the action
	 * @return  mixed
	 */
	public function domainTemplatepath($domainTemplatepath = NULL)
	{
		if ($domainTemplatepath === NULL)
		{
			// Act as a getter
			return self::$domain_templatepath;
		}

		// Act as a setter
		self::$domain_templatepath = (string) $domainTemplatepath;

		return $this;
	}
}