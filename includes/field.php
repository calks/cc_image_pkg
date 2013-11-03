<?php

	require_once Application::getSitePath() . '/packages/file/includes/field.php';

	class TEntityImagesField extends TEntityFilesField {
		

		function GetAsHTML() {
			
			if (!$this->Value) $this->Value = $this->hash;
			
			$hidden_field_html = THiddenField::GetAsHTML();
			
			$iframe_src = $this->getIframeSrc();
			$iframe_width = isset($this->params['width']) ? $this->params['width'] : 800;
			$iframe_height = isset($this->params['height']) ? $this->params['height'] : 300;
			
			echo $hidden_field_html;

			echo "<iframe src=\"$iframe_src\" width=\"$iframe_width\" height=\"$iframe_height\" border=0 style=\"border: none\"></iframe>";
			
		}
		
		function getIframeSrc() {
			return Application::getSeoUrl("/image_upload/$this->Value");
		}
		
		
	}