<?php
	
	class imagePkgFormElementsAddonImageField extends coreFormElementsAddonBaseField {
	
		protected $entity_name; 
		protected $entity_id;
		protected $width = 800;
		protected $height = 300;
		protected $max_files;
		protected $entity_existance_check = true;
		protected $valid_extensions = array(
			'jpg',
			'jpeg',
			'gif',
			'png'
		);
		
		
		public function __construct($name) {
			parent::__construct($name);
			$this->SetValue(md5(uniqid()));						
		}
		
		public function setValue($value) {
			if (!$value) return;
			parent::setValue($value);
			//echo('setValue(' . $value . ')');
		}
		
		public function GetAsHTML() {
			//echo '*' . $this->value . '*';
			$session_name = $this->getSessionName();
			if (!isset($_SESSION[$session_name])) $_SESSION[$session_name] = array();
			
			$_SESSION[$session_name][$this->value] = array(
				'field_name' => $this->field_name,
				'entity_name' => $this->entity_name,
				'entity_id' => $this->entity_id,
				'params' => array(
					'valid_extensions' => $this->valid_extensions,
					'max_files' => $this->max_files,
					'entity_existance_check' => $this->entity_existance_check
				)
			);
			
			
			$hidden_field_html = "<input type=\"hidden\" name=\"$this->field_name\" value=\"$this->value\" />";
			
			$iframe_src = $this->getIframeSrc();
			$iframe_width = $this->width;
			$iframe_height = $this->height;
			$iframe_name = 'image_upload_' . md5($session_name);

			$iframe_html = "<iframe name=\"$iframe_name\" src=\"$iframe_src\" width=\"$iframe_width\" height=\"$iframe_height\" border=0 style=\"border: none\"></iframe>";
			return "$hidden_field_html $iframe_html";			
		}
		
		function getIframeSrc() {
			return Application::getSeoUrl("/image_upload/$this->value");
		}
		
		protected function getSessionName() {
			return md5('file_uploader_fields');			
		}
		
		
		
	}