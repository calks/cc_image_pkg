<?php

	require_once Application::getSitePath() . '/packages/file/includes/field.php';

	class TEntityImagesField extends TEntityFilesField {
		

		
		function getIframeSrc() {
			return Application::getSeoUrl("/image_upload/$this->Value");
		}
		
		
	}