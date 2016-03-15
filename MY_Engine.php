<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Engine {
	
	protected $CI;
	
	protected $my_module_path;
	
	protected $my_prefix = 'gen_';
	
	private function my_mkdir($path)
	{
		if (! file_exists($path)) {
			mkdir($path);
		}
	}
	
	public function __construct() 
	{
		$this->CI =& get_instance();
		$this->my_module_path = APPPATH.'modules\\';
		$this->my_mkdir($this->my_module_path);
	}
	
	public function my_mkmod($name)
	{
		$name = $this->my_prefix . $name;
		
		$module = $this->my_module_path . $name;
		$controllers = $module .'\\controllers';
		$models = $module .'\\models';
		$views = $module .'\\views';

		$this->my_mkdir($module);
		$this->my_mkdir($controllers);
		$this->my_mkdir($models);
		$this->my_mkdir($views);
		$this->my_mkdir($views.'\\public');
		$this->my_mkdir($views.'\\admin');
	}
	
	public function my_c_path($name) 
	{
		$name = $this->my_prefix . $name;
		
		$module = $this->my_module_path . $name;
		$controllers = $module .'\\controllers';
		
		return $controllers;
	}
 
	public function my_m_path($name) 
	{
		$name = $this->my_prefix . $name;
		
		$module = $this->my_module_path . $name;
		$models = $module .'\\models';
		
		return $models;
	}
	
	public function my_v_path($name) 
	{
		$name = $this->my_prefix . $name;
		
		$module = $this->my_module_path . $name;
		$views = $module .'\\views';
		
		return $views;
	}
	
	public function my_f_form($table) {
		$data = array();
		if ($this->CI->db->table_exists($table)) {
			$arr = $this->CI->db->list_fields($table);
			foreach($arr as $k => $v) {
				$data[$v] = "f_".$v;
			}
			unset($data[$table.'_status']);
		}
		return $data;
	}
	
	public function my_p_form($data_field) {
		$text = "";
		foreach ($data_field as $k => $v) {
			if (preg_match('/(_gambar|_foto|_image|_file|_video)$/', $k, $matches)) {
				$upload="";
				switch ($matches[0]) {
					case "_gambar":
					case "_foto":
					case "_image":
						$upload = "\$this->do_upload_image";
					break;
					case "_video":
						$upload = "\$this->do_upload_video";
					break;		
					case "_file":
						$upload = "\$this->do_upload_file";
					break;		
				}
				$text .= "\n\t\tif ((\$filename = $upload('$v', '$k')) != '') {";
				$text .= "\n\t\t\t\$data['$k'] = \$filename;";	
				$text .= "\n\t\t}";
			} else {
				$text .= "\n\t\t\$data['$k'] = \$this->input->post('$v');";	
			}
		}
		$text .= "\n";
		return $text;
	}

	public function my_v_form($data_field) {
		$text = "\n\t\t\$this->form_validation->set_error_delimiters('&lt;br /&gt;&lt;span class=\"error\"&gt;', '&lt;/span&gt;');";
		foreach ($data_field as $k => $v) {
			if (! preg_match('/(_gambar|_foto|_image|_file|_video)$/', $k)) {
				$ket = ucfirst(str_replace('_', ' ', $k));
				$text .= "\n\t\t\$this->form_validation->set_rules('$v', '$ket', 'required');";
			}
		}
		$text .= "\n";
		return $text;		
	}
	
	public function my_d_form($data_field) {
		$data = array();
		
		$data['multipart'] = "";
		foreach ($data_field as $k => $v) {
			if (preg_match('/^(id_)/', $k)) {
				$data[$k] = "&lt;input type=\"hidden\" name=\"$v\" value=\"&lt;?php echo \$row->$k; ?&gt;\" />";
			} else if (preg_match('/(_gambar|_foto|_image|_file|_video)$/', $k, $matches)) {
				$label = ucfirst(substr($matches[0], 1));
				$data['multipart'] = "accept-charset=\"utf-8\" enctype=\"multipart/form-data\"";
				$data[$k]  = "\n\t&lt;div class=\"form-group\"&gt;";
				$data[$k] .= "\n\t&lt;label class=\"control-label col-sm-2\"&gt;$label&lt;/label&gt;";
				$data[$k] .= "\n\t&lt;div class=\"col-sm-10\"&gt;";
				$data[$k] .= "\n\t\t&lt;input type=\"file\" name=\"$v\" value=\"\" />";
				$data[$k] .= "\n\t\t&lt;?php echo form_error('$v'); ?&gt;";
				$data[$k] .= "\n\t&lt;/div&gt;";
				$data[$k] .= "\n\t&lt;/div&gt;";
			} else if (preg_match('/(_isi|_konten|_content|_text)$/', $k)) {
				$label = ucfirst(str_replace('_', ' ', $k));
				$data[$k]  = "\n\t&lt;div class=\"form-group\"&gt;";
				$data[$k] .= "\n\t&lt;label class=\"control-label col-sm-2\"&gt;$label&lt;/label&gt;";
				$data[$k] .= "\n\t&lt;div class=\"col-sm-10\"&gt;";
				$data[$k] .= "\n\t\t&lt;textarea class=\"form-control ckeditor\" rows=\"10\" name=\"$v\" &gt;&lt;?php echo @\$row->$k; ?&gt;&lt;/textarea&gt;";
				$data[$k] .= "\n\t\t&lt;?php echo form_error('$v'); ?&gt;";
				$data[$k] .= "\n\t&lt;/div&gt;";
				$data[$k] .= "\n\t&lt;/div&gt;";
			} else {
				$datepicker = '';
				if (preg_match('/(_tgl|_tanggal|_date)/', $k)) {
					$datepicker = ' data-provide="datepicker" data-date-format="yyyy-mm-dd" ';
				}
				$tagsinput = '';
				if (preg_match('/(_tag|_tags)/', $k)) {
					$tagsinput = ' data-role="tagsinput" ';
				}
				$label = ucfirst(str_replace('_', ' ', $k));
				$data[$k]  = "\n\t&lt;div class=\"form-group\"&gt;";
				$data[$k] .= "\n\t&lt;label class=\"control-label col-sm-2\"&gt;$label&lt;/label&gt;";
				$data[$k] .= "\n\t&lt;div class=\"col-sm-10\"&gt;";
				$data[$k] .= "\n\t\t&lt;input class=\"form-control\" type=\"text\" name=\"$v\" $datepicker $tagsinput value=\"&lt;?php echo @\$row->$k; ?&gt;\" /&gt;";
				$data[$k] .= "\n\t\t&lt;?php echo form_error('$v'); ?&gt;";
				$data[$k] .= "\n\t&lt;/div&gt;";
				$data[$k] .= "\n\t&lt;/div&gt;";
			}
		}
		return $data;
	}
	
	public function my_cek_upload($data_field) 
	{
		$return = false;
		foreach ($data_field as $k => $v) {
			if (preg_match('/(_gambar|_foto|_image|_file|_video)$/', $k)) {
				$return = true;
			}
		}
		return $return;
	}

}