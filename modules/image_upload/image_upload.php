<?php

	class imagePkgImageUploadModule extends filePkgFileUploadModule {
		
		protected function getEntityName() {
			return 'image';
		}

		
		public function run($params = array()) {
			$smarty = Application::getSmarty();
			
			$file_upload_css = coreResourceLibrary::getFirstFilePath(APP_RESOURCE_TYPE_MODULE, 'file_upload', '/static/css/style.css');
			$smarty->assign('file_upload_css', $file_upload_css);
			
			return parent::run($params);
		}
		
		protected function getValidExtensions() {
			return isset($this->params['valid_extensions']) ? $this->params['valid_extensions'] : array(
				'jpg',
				'jpeg',
				'gif',
				'png'
			);
		}
		
		protected function getFilesList() {
			$files = parent::getFilesList();
			
			foreach ($files as $file) {
				$file->thumbnail_url = imagePkgHelperLibrary::getThumbnailUrl($file->id, 80, 80, 'crop');
			}
			
			return $files;
		}
		
		
		protected function populateDbRecord(&$file) {			
			parent::populateDbRecord($file);
			
			$stored_file_directory = $this->getStoredFileDirectory($file->stored_filename);
			$stored_file_path = Application::getSitePath() . $stored_file_directory . '/' . $file->stored_filename;
			
			$size = getimagesize($stored_file_path);
			$file->width = $size[0];
			$file->height = $size[1];			
		}
		
		
	}