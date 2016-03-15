<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_mail
{
	private $_ci;
	
	private $_layout_location;
	
	private $_layout_name;
	
	private $_from;
	
	private $_alias;
	
	private function _loadConfig($config = array()) {
		if ( ! empty($config))
		{
			foreach ($config as $key => $val)
			{
				$this->{'_'.$key} = $val;
			}
		}		
	}	
	function __construct($config = array())
	{
		$this->_ci =& get_instance();
		$this->_layout_location = APPPATH."my_mail/";
		if ( ! empty($config))
		{
			$this->_loadConfig($config['default']);
		}

		log_message('debug', 'MY_mail Class Initialized');
	}
	
	function load($layout, $data)
	{
		$file_layout = '';
		$this->_layout_name = $layout.'.php';
		if(file_exists($this->_layout_location.$this->_layout_name)) {
			$file_layout = $this->_layout_location.$this->_layout_name;
		} else {
			$file_layout = $this->_layout_location.'default.php';
		}
		$this->_ci->load->vars($data);
		$body = $this->_ci->load->file($file_layout, TRUE);

		return $body;
	}
	
	function send($layout, $data, $to, $from='default') 
	{
		$return = "";
		$mymail = $this->_ci->load->config('my_mail', TRUE, TRUE);
		if ($mymail != null) {
			$config = $this->_ci->config->item($from, 'my_mail');
			$this->_loadConfig($config);
			
			$file_layout = '';
			$this->_layout_name = $layout.'.php';
			if(file_exists($this->_layout_location.$this->_layout_name)) {
				$file_layout = $this->_layout_location.$this->_layout_name;
			} else {
				$file_layout = $this->_layout_location.'default.php';
			}
			$this->_ci->load->vars($data);
			$body = $this->_ci->load->file($file_layout, TRUE);
			$this->_ci->load->library('email');
			$this->_ci->email->from($this->_from, $this->_alias);
			$this->_ci->email->to($to);
			$this->_ci->email->subject($data['subject']);
			$this->_ci->email->message($body);
			$this->_ci->email->send();				
			return $body;
		}
	}

}