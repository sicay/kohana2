<?php defined('SYSPATH') or die('No direct script access.');

class Form_Checkbox_Core extends Form_Input {

	protected $data = array
	(
		'type' => 'checkbox',
		'class' => 'checkbox',
		'value' => '1',
		'checked' => FALSE,
	);

	protected $protect = array('type');

	public function __get($key)
	{
		if ($key == 'value')
		{
			// Return the value if the checkbox is checked
			return $this->data['checked'] ? $this->data['value'] : NULL;
		}

		return parent::__get($key);
	}

	public function label($val = NULL)
	{
		if ($val === NULL)
		{
			return '';
		}
		else
		{
			$this->data['label'] = ($val === TRUE) ? ucwords(inflector::humanize($this->name)) : $val;
			return $this;
		}
	}

	protected function html_element()
	{
		// Import the data
		$data = $this->data;

		if ($label = arr::remove('label', $data))
		{
			// There must be one space before the text
			$label = ' '.ltrim($label);
		}

		return '<label>'.form::checkbox($data).$label.'</label>';
	}

	protected function load_value()
	{
		if (empty($_POST))
			return;

		// Makes the box checked if the value from POST is the same as the current value
		$this->data['checked'] = (self::$input->post($this->name) == $this->data['value']);
	}

} // End Form Checkbox