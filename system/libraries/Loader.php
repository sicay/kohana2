<?php defined('SYSPATH') or die('No direct access allowed.');

class Core_Loader {

	function autoload()
	{
		foreach(Config::load('autoload') as $type => $load)
		{
			if (count($load) < 1) continue;

			switch($type)
			{
				case 'helpers':
					$this->helper($load);
				break;
			}
		}
	}

	function library($name)
	{
		Kohana::$instance->$name = Kohana::load_class($name);
	}

	function helper($name)
	{
		if (is_array($name) AND $helpers = $name)
		{
			foreach($helpers as $name)
			{
				$this->helper($name);
			}
		}
		else
		{
			include Kohana::find_file('helpers', $name, TRUE);
		}
	}

	function model($name)
	{
		Kohana::$instance->$name = Kohana::load_class($name.'_Model');
	}

	// Weird prefixes to prevent collisions
	function view($name, $data = array())
	{
		return new View($name, $data);
		$kohana_filename = Kohana::find_file('views', $name, TRUE);

		ob_start();
		extract($kohana_data);
		include $kohana_filename;
		$kohana_output = ob_get_contents();
		ob_end_clean();

		if ($kohana_return == TRUE)
		{
			return $kohana_output;
		}
		else
		{
			print $kohana_output;
		}
	}

} // End Loader Class