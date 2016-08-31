<?php

	class imagePkgImageSettingGroup extends coreBaseSettingGroup {
		
		public function getGroupNames() {
			return array(
				'image' => 'Images'
			);
		}
		
		public function getParamsTree() {
			$out = array(
				'autorotate' => array(
					'type' => 'checkbox',
					'displayed_name' => 'Use EXIF orientation data for autorotation'					
				)				
			);
			
			return array('image' => $out);
		
		}
	}