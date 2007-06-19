<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @author      Rick Ellis
 * @category    Libraries
 * @link        http://kohanaphp.com/user_guide/general/routing.html
 */
class Core_Router {

	var $config;
	var $uri_string   = '';
	var $segments     = array();
	var $rsegments    = array();
	var $routes       = array();
	var $error_routes = array();
	var $class        = '';
	var $method       = 'index';
	var $directory    = '';
	var $uri_protocol = 'auto';
	var $default_controller;
	var $scaffolding_request = FALSE; // Must be set to FALSE

	/**
	 * Constructor
	 *
	 * Runs the route mapping function.
	 */
	function Core_Router()
	{
		$this->config =& load_class('Config');
		$this->_set_route_mapping();
		log_message('debug', 'Router Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Set the route mapping
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @access	private
	 * @return	void
	 */
	function _set_route_mapping()
	{
		// Are query strings enabled in the config file?
		// If so, we're done since segment based URIs are not used with query strings.
		if ($this->config->item('enable_query_strings') === TRUE AND isset($_GET[$this->config->item('controller_trigger')]))
		{
			$this->set_class($_GET[$this->config->item('controller_trigger')]);

			if (isset($_GET[$this->config->item('function_trigger')]))
			{
				$this->set_method($_GET[$this->config->item('function_trigger')]);
			}
			return;
		}

		// Load the routes.php file.
		@include(APPPATH.'config/routes'.EXT);
		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
		unset($route);

		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);

		// Fetch the complete URI string
		$this->uri_string = '/'.trim($this->_get_uri_string(), '/');

		// If the URI contains only a slash we'll kill it
		if ($this->uri_string == '/')
		{
			$this->uri_string = '';
		}

		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
		if ($this->uri_string == '')
		{
			if ($this->default_controller === FALSE)
			{
				show_error("Unable to determine what should be displayed. A default route has not been specified in the routing file.");
			}

			$this->set_class($this->default_controller);
			$this->set_method('index');

			log_message('debug', 'No URI present. Default controller set.');
			return;
		}
		unset($this->routes['default_controller']);

		// Do we need to remove the suffix specified in the config file?
		if  ($this->config->item('url_suffix') != '')
		{
			$this->uri_string = preg_replace('|'.preg_quote($this->config->item('url_suffix')).'$|', '', $this->uri_string);
		}

		// Explode the URI Segments. The individual segments will
		// be stored in the $this->segments array.
		foreach(explode('/', trim($this->uri_string, '/')) as $val)
		{
			// Filter segments for security
			$val = $this->_filter_uri($val);

			if ($val != '')
			{
				$this->segments[] = $val;
			}
		}

		// Parse any custom routing that may exist
		$this->_parse_routes();

		// Re-index the segment array so that it starts with 1 rather than 0
		$this->_reindex_segments();
	}

	// --------------------------------------------------------------------

	/**
	 * Compile Segments
	 *
	 * This function takes an array of URI segments as
	 * input, and puts it into the $this->segments array.
	 * It also sets the current class/method
	 *
	 * @access	private
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function _compile_segments($segments = array())
	{
		$segments = $this->_validate_segments($segments);

		if (count($segments) == 0)
			return;

		$this->set_class($segments[0]);

		if (isset($segments[1]))
		{
			// A scaffolding request. No funny business with the URL
			if ($this->routes['scaffolding_trigger'] == $segments[1] AND $segments[1] != '_ci_scaffolding')
			{
				$this->scaffolding_request = TRUE;
				unset($this->routes['scaffolding_trigger']);
			}
			else
			{
				// A standard method request
				$this->set_method($segments[1]);
			}
		}

		// Update our "routed" segment array to contain the segments.
		// Note: If there is no custom routing, this array will be
		// identical to $this->segments
		$this->rsegments = $segments;
	}

	// --------------------------------------------------------------------

	/**
	 * Validates the supplied segments.  Attempts to determine the path to
	 * the controller.
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */
	function _validate_segments($segments)
	{
		// Does the requested controller exist in the root folder?
		if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);

			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
				{
					show_404();
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');

				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}

			}

			return $segments;
		}

		// Can't find the requested controller...
		show_404();
	}

	// --------------------------------------------------------------------
	/**
	 * Re-index Segments
	 *
	 * This function re-indexes the $this->segment array so that it
	 * starts at 1 rather then 0.  Doing so makes it simpler to
	 * use functions like $this->uri->segment(n) since there is
	 * a 1:1 relationship between the segment array and the actual segments.
	 *
	 * @access	private
	 * @return	void
	 */
	function _reindex_segments()
	{
		// Is the routed segment array different then the main segment array?
		$reindex = ($this->rsegments == $this->segments) ? array('segments') : array('segments', 'rsegments');

		// Reindex segments by adding a NULL to key 0, reindexing with
		// array_values, then removing key 0
		foreach($reindex as $array)
		{
			$segments =& $this->$array;
			array_unshift($segments, NULL);
			$segments = array_values($segments);
			unset($segments[0]);
		}

		if (count($reindex) < 2)
		{
			$this->rsegments = $this->segments;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get the URI String
	 *
	 * @access	private
	 * @return	string
	 */
	function _get_uri_string()
	{
		if (strtoupper($this->config->item('uri_protocol')) == 'AUTO')
		{
			// The following two protocols are incompatible with "enable_get_requests"
			// Note: some servers seem to have trouble with getenv() so we'll test it two ways
			if ($this->config->item('enable_get_requests') == FALSE)
			{
				// If the URL has a question mark then it's simplest to just
				// build the URI string from the zero index of the $_GET array.
				// This avoids having to deal with $_SERVER variables, which
				// can be unreliable in some environments
				if (is_array($_GET) AND count($_GET) == 1)
				{
					// Note: Due to a bug in current() that affects some versions
					// of PHP we can not pass function call directly into it
					$keys = array_keys($_GET);
					return current($keys);
				}

				// No $_GET? What about QUERY_STRING?
				$path = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
				if ($path != '')
					return $path;
			}

			// Is there a PATH_INFO variable?
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
			if ($path != '' AND $path != "/".SELF)
				return $path;

			// No PATH_INFO?... Maybe the ORIG_PATH_INFO variable exists?
			$path = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');
			if ($path != '' AND $path != "/".SELF)
				return $path;

			// We've exhausted all our options
			return '';
		}
		else
		{
			$uri = strtoupper($this->config->item('uri_protocol'));

			if ($uri == 'REQUEST_URI')
				return $this->_parse_request_uri();

			return (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Parse the REQUEST_URI
	 *
	 * Due to the way REQUEST_URI works it usually contains path info
	 * that makes it unusable as URI data.  We'll trim off the unnecessary
	 * data, hopefully arriving at a valid URI that we can use.
	 *
	 * @access	private
	 * @return	string
	 */
	function _parse_request_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR $_SERVER['REQUEST_URI'] == '')
			return '';

		$request_uri = trim(str_replace("\\", "/", $_SERVER['REQUEST_URI']), '/');

		if ($request_uri == '' OR $request_uri == SELF)
			return '';

		$fc_path = FCPATH;
		if (strpos($request_uri, '?') !== FALSE)
		{
			$fc_path .= '?';
		}

		$parsed_uri = explode("/", $request_uri);

		$i = 0;
		foreach(explode("/", $fc_path) as $segment)
		{
			if (isset($parsed_uri[$i]) AND $segment == $parsed_uri[$i])
				$i++;
		}

		$parsed_uri = implode("/", array_slice($parsed_uri, $i));

		if ($parsed_uri != '')
		{
			$parsed_uri = '/'.$parsed_uri;
		}

		return $parsed_uri;
	}

	// --------------------------------------------------------------------

	/**
	 * Filter segments for malicious characters
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _filter_uri($str)
	{
		$str = rawurldecode($str);

		if ($this->config->item('enable_get_requests'))
		{
			$end = strpos($str, '?');
			$str = ($end !== FALSE) ? substr($str, 0, $end) : $str;
		}

		if ($this->config->item('permitted_uri_chars') != '')
		{
			if ( ! preg_match('|^['.preg_quote($this->config->item('permitted_uri_chars')).']+$|i', $str))
			{
				header('HTTP/1.1 400 Bad Request');
				exit('The URI you submitted has disallowed characters.');
			}
		}

		return trim($str);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse Routes
	 *
	 * This function matches any routes that may exist in
	 * the config/routes.php file against the URI to
	 * determine if the class/method need to be remapped.
	 *
	 * @access	private
	 * @return	void
	 */
	function _parse_routes()
	{
		// Do we even have any custom routing to deal with?
		// There is a default scaffolding trigger, so we'll look just for 1
		if (count($this->routes) == 1)
		{
			return $this->_compile_segments($this->segments);
		}

		// Turn the segment array into a URI string
		$uri = implode('/', $this->segments);

		// Is there a literal match?  If so we're done
		if (isset($this->routes[$uri]))
		{
			return $this->_compile_segments(explode('/', $this->routes[$uri]));
		}

		// Loop through the route array looking for wild-cards
		foreach (array_slice($this->routes, 1) as $key => $val)
		{
			// Convert wild-cards to RegEx
			$key = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $key);

			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri))
			{
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}

				return $this->_compile_segments(explode('/', $val));
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_compile_segments($this->segments);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the class name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_class($class)
	{
		$this->class = $class;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current class
	 *
	 * @access	public
	 * @return	string
	 */
	function fetch_class()
	{
		return $this->class;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set the method name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_method($method)
	{
		$this->method = $method;
	}

	// --------------------------------------------------------------------

	/**
	 *  Fetch the current method
	 *
	 * @access	public
	 * @return	string
	 */
	function fetch_method()
	{
		if ($this->method == $this->fetch_class())
		{
			return 'index';
		}

		return $this->method;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set the directory name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_directory($dir)
	{
		$this->directory = $dir.'/';
	}

	// --------------------------------------------------------------------

	/**
	 *  Fetch the sub-directory (if any) that contains the requested controller class
	 *
	 * @access	public
	 * @return	string
	 */
	function fetch_directory()
	{
		return $this->directory;
	}

}
// END Router Class
?>