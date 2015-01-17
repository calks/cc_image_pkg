<?php

	class imagePkgHelperLibrary extends filePkgHelperLibrary {
		
		
		public static function getField($aName, $entity_name=null, $entity_id=null, $params=array()) {
			require_once Application::getSitePath() . '/packages/image/includes/field.php';
						
			$field = new TEntityImagesField($aName, $entity_name, $entity_id, $params);

			return $field;
		}
				
		
		public static function getFilesCount($field_hash) {			
			return Application::runModule('image_upload', array($field_hash, 'count'));
		}
		
		public static function commitUploadedFiles($field_hash, $entity_id) {
			Application::runModule('image_upload', array($field_hash, 'commit', $entity_id));			
		}
		
		public static function getThumbnailDir($image_id) {			
			
			$dir = Application::getTempDirectory() . "/thumb";
			if ($image_id > 100) {
				$subdir = $image_id%100;
				$dir .= "/$subdir";	
			}		
			$dir .= "/$image_id";	
			$path = Application::getSitePath() . $dir;
			if (!is_dir($path)) {
				if (!@mkdir($path, 0777, true)) {
					die("Can't create thumbnail dir");
				}
			}
						
			return $dir;
			
		}
		
		public static function getThumbnailRelativePath($image_id, $width, $height, $mode='inscribe', $output_format='jpeg', $generate_if_not_available=false) {					
			$path = self::getThumbnailDir($image_id) . "/{$width}x{$height}_$mode.$output_format";
			if ($generate_if_not_available) {
				$file_available = is_file(Application::getSiteUrl() . $path); 
				if (!$file_available) {
					$temp_path = Application::getSitePath() . Application::getTempDirectory() . '/'.md5(uniqid());					
					copy(Application::getSiteUrl() . $path, $temp_path);
					unlink($temp_path);
				}
			}			
			return $path;
		}
		
		public static function getThumbnailUrl($image_id, $width, $height, $mode='inscribe', $output_format='jpeg', $generate_if_not_available=false) {
			return Application::getSiteUrl() . self::getThumbnailRelativePath($image_id, $width, $height, $mode, $output_format, $generate_if_not_available); 
		}
		
		
		public static function loadFilesCount(&$entity_or_array, $count_fieldname, $storage_entity_name='image') {
			parent::loadFilesCount($entity_or_array, $count_fieldname, $storage_entity_name);
		}
		
		
		public static function loadImages(&$entity_or_array, $list_fieldname, $storage_entity_name='file') {
			filePkgHelperLibrary::loadFiles($entity_or_array, $list_fieldname, 'image');
		}
		
		public static function deleteImages($entity, $storage_entity_name='image') {
			return parent::deleteFiles($entity, $storage_entity_name);
		}
		
		public static function copyExistingFile($entity, $file_path, $field_name) {
			$file_id = filePkgHelperLibrary::copyExistingFile($entity, $file_path, $field_name);
			if (!$file_id) return;
			
			$file = Application::getEntityInstance('file');
			$file = $file->load($file_id);
			
			$image = Application::getEntityInstance('image');
			$image->entity_name = $file->entity_name;
			$image->entity_id = $file->entity_id;
			$image->created = $file->created;
			$image->stored_filename = $file->stored_filename;
			$image->original_filename = $file->original_filename;		
			$image->mime_type = $file->mime_type;
			$image->size = $file->size;
			$image->seq = $file->seq;
			$image->field_hash = $file->field_hash;
			$image->is_temporary = $file->is_temporary;
			$image->field_name = $file->field_name;
			
			$file_path = Application::getSiteUrl() . filePkgHelperLibrary::getStorageDirectory($file->stored_filename) . '/' . $file->stored_filename;
			$dimensions = @getimagesize($file_path);
			$image->width = isset($dimensions[0]) ? $dimensions[0] : 0;
			$image->height = isset($dimensions[1]) ? $dimensions[1] : 0;
			
			$image_id = $image->save();
			
			$db = Application::getDb();			
			$db->execute("
				DELETE FROM {$file->getTableName()}
				WHERE id=$file->id
			");
			
			return $image_id;			
		}
		
	}
	
	
	