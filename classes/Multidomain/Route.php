<?php defined('SYSPATH') or die('No direct script access.');

class Multidomain_Route extends Kohana_Route {

	/**
	 * translates the uri
	 *
	 * @param   string  $uri    URI to match
	 * @return  array   on success
	 * @return  FALSE   on failure
	 */
	public function matches(Request $request)
	{
		Multidomain_Route_Translate::instance()->decodeUri($request);

		return parent::matches($request);
	}

	/**
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   $params URI parameters
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_Key
	 */
	public function uri(array $params = NULL) {
		$uri = parent::uri($params);

		$uri = Multidomain_Route_Translate::instance()->translateURI($uri, $params);

		return $uri;
	}
}