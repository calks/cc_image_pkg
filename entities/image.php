<?php 


	class imagePkgImageEntity extends filePkgFileEntity {
		
		public $width;
		public $height;
		
		public function getTableName() {			
			return 'images';
		}
		
		
		
	}

