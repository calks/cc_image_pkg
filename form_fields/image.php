<?php
	
	class imagePkgImageFormField extends filePkgFileFormField {

		
		public function __construct($name) {
			parent::__construct($name);			
			$this->setValidExtensions(array(
				'jpg',
				'jpeg',
				'gif',
				'png'
			));
			
			$this->addClass('image-form-field');
		}
		
		
		protected function getIframeSrc() {
			return Application::getSeoUrl("/image_upload/$this->value");
		}
		
		
		
	}