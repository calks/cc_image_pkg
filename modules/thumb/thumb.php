<?php

	class imagePkgThumbModule extends coreBaseModule {
		
		protected $image_id;
		protected $image;
		protected $source_path;
		protected $width;
		protected $height;
		protected $mode;
		protected $output_format;
		
		
		protected function getDestinationPath() { 
			return Application::getSitePath() . imagePkgHelperLibrary::getThumbnailRelativePath($this->image_id, $this->width, $this->height, $this->mode, $this->output_format);
		}
		
		protected function parseImageParams($thumb_filename) {
			$info = explode('.', $thumb_filename);
			$this->output_format = isset($info[1]) ? $info[1] : filePkgHelperLibrary::getFileExtension($this->image->original_filename);
			if ($this->output_format == 'jpg') $this->output_format = 'jpeg';
			
			if (!in_array($this->output_format, array('jpeg', 'gif', 'png'))) return false;
			
			$info = $info[0];
			$info = explode('_', $info);
			$this->mode = isset($info[1]) ? $info[1] : 'inscribe'; 
			$dimensions = isset($info[0]) ? $info[0] : null;
			
			if (!$dimensions) return false;
			
			$dimensions = explode('x', $dimensions);
			
			$this->width = isset($dimensions[0]) ? (int)$dimensions[0] : 0;
			$this->height = isset($dimensions[1]) ? (int)$dimensions[1] : 0;
			
			if (!$this->height || !$this->width) return false;
			
			return true;		
		}
		
		
		protected function redirectToImage() {
			$redirect_url = imagePkgHelperLibrary::getThumbnailUrl($this->image_id, $this->width, $this->height, $this->mode, $this->output_format);			
			Redirector::redirect($redirect_url);
		}
		
		public function run($params=array()) {			
			
			$this->image_id = @array_shift($params);
			if (count($params)==2) $this->image_id = @array_shift($params);
						
			if ((int)$this->image_id == 0) {
				$this->image_id = str_replace(array('/', '\\'), '', $this->image_id);
				
				/*$t = coreResourceLibrary::getFirstFilePath(APP_RESOURCE_TYPE_MODULE, $this->getName(), '/static/no_image');
				print_r($t);*/
				
				$this->source_path = Application::getSitePath() . coreResourceLibrary::getFirstFilePath(APP_RESOURCE_TYPE_MODULE, $this->getName(), "/static/no_image/{$this->image_id}.png");
				if (!is_file($this->source_path)) {
					$this->source_path = Application::getSitePath() . coreResourceLibrary::getFirstFilePath(APP_RESOURCE_TYPE_MODULE, $this->getName(), "/static/no_image/default.png");
				}				
			}
			else {				
				$this->image_id = (int)$this->image_id;				
				$image = Application::getEntityInstance('image');
				$this->image = $image->load($this->image_id);

				if (!$this->image) return $this->terminate();
				
				$this->source_path = Application::getSitePath() . imagePkgHelperLibrary::getStorageDirectory($this->image->stored_filename) . '/' . $this->image->stored_filename;
			}
			//echo $this->source_path;die();

			if (!is_file($this->source_path)) return $this->terminate();
			
			
			$thumb_filename = @array_shift($params);
			
			if (!$this->parseImageParams($thumb_filename)) return $this->terminate();
			
			$destination_path = $this->getDestinationPath();			
			
			$info = getimagesize($this->source_path);
			if ($info[2] == IMAGETYPE_JPEG) {
				if (function_exists('imagecreatefromjpeg')) $img = imagecreatefromjpeg($this->source_path);
				else return $this->terminate();
			} elseif ($info[2] == IMAGETYPE_GIF) {
				if (function_exists('imagecreatefromgif')) $img = imagecreatefromgif($this->source_path);
				else return $this->terminate();	
			}
        	elseif ($info[2] == IMAGETYPE_PNG) {
        		if (function_exists('imagecreatefrompng')) $img = imagecreatefrompng($this->source_path);
        		else return $this->terminate();
        	}
        	else return $this->terminate();
        	
        	
        	
			$autorotate_angle = $this->getAngleForAutorotate();
			if (abs($autorotate_angle) == 90) {
        		$src_width = $info[1];
        		$src_height = $info[0];				
			}
			else {
        		$src_width = $info[0];
        		$src_height = $info[1];
			}
			
			
			if ($autorotate_angle) {
				$img = imagerotate($img, $autorotate_angle, 0);
			}
			
                
			if (in_array($this->mode, array('crop', 'fit-crop'))) { 
		        $dims = $this->retainAspectRatio($this->mode, $src_width, $src_height, $this->width, $this->height);

		        $out_width = $this->width;
		        $out_height = $this->height;
		        $dest_width = $dims[0];
		        $dest_height = $dims[1];
	        
				$srcLeft = ceil(($dest_width-$this->width)/80);
	        
	        
		        if ($dest_height>$this->height) {
		        	$srcTop = ceil(($dest_height-$this->height));
		        	$destTop = 0;	
		        }
		        else {
		        	$srcTop = 0;
		        	$destTop = ceil(($this->height-$dest_height)/2);	        	
		        }
		        
		        if ($dest_width>$this->width) {		        	
		        	$srcLeft = 0;
		        	$destLeft = -1 * floor(($dest_width-$this->width)/2);	
		        }
		        else {
		        	$srcLeft = 0;
		        	$destLeft = ceil(($this->width-$dest_width)/2);	        	
		        }	        
	        }
    	    else {
		        if ($info[0] > $this->width || $info[1] > $this->height) {
		        	$dims = $this->retainAspectRatio($this->mode, $src_width, $src_height, $this->width, $this->height);
		        }
		        else {
		        	$dims = array($info[0], $info[1]);
		        }
		        $srcLeft = 0;
		        $srcTop = 0;
		        $destLeft = 0;
		        $destTop = 0;
		        $out_width = $dims[0];
		        $out_height = $dims[1];
		        $dest_width = $dims[0];
		        $dest_height = $dims[1];
		        
	        }

	        $ne = $this->getEmptyImage($out_width, $out_height);

        	imagecopyresampled($ne, $img, $destLeft, $destTop, $srcLeft, $srcTop, $dest_width, $dest_height, $src_width, $src_height);
        	

//        if ($watermark) $ne = createWatermark($ne);

			switch ($this->output_format) {
				case 'jpeg':
            	$func = 'imagejpeg';
            	$type = 'image/jpeg';
            	break;
        	case 'gif':
            	$func = 'imagegif';
            	$type = 'image/gif';
            	break;
        	case 'png':
            	$func = 'imagepng';
            	$type = 'image/png';
            	break;
        	}

        	$this->addWatermark($ne);
        	
        	call_user_func($func, $ne, $destination_path);
        	
        	/*header("Content-type: $type\n");
        	imagejpeg($ne);
        	die();*/

        	if (is_file($destination_path)) $this->redirectToImage();
        	else return $this->terminate();        	
		}
		
		
		protected function getAngleForAutorotate() {
			
			$autorotation_enabled = coreSettingsLibrary::get('image/autorotate');
			if (!$autorotation_enabled) return 0;
						
			/*
			 * Orientation values:
			 * 6 - rotated 90 degrees CCW 
			 * 8 - rotated 90 degrees CW
			 * 3 - up side down
			 * 1 - normal
			 *  
			 */
			
			$exif = @exif_read_data($this->source_path);
			if (!$exif) return 0;
			$orientation = isset($exif['Orientation']) ? $exif['Orientation'] : null; 
			
			switch ($orientation) {
				case 6:
					return -90;
				case 8:
					return 90;
				case 3:
					return 180;
				default:
					return 0;
			}
		}
		
		protected function addWatermark($image) {
			$watermark_file = coreResourceLibrary::getFirstFilePath(APP_RESOURCE_TYPE_MODULE, $this->getName(), "/static/watermark");
			if (!$watermark_file) return;
			
			$watermark_path = Application::getSitePath() . $watermark_file;
			
			$info = getimagesize($watermark_path);
			if ($info[2] == IMAGETYPE_JPEG) {
				if (function_exists('imagecreatefromjpeg')) $watermark = imagecreatefromjpeg($watermark_path);
				else return;
			} elseif ($info[2] == IMAGETYPE_GIF) {
				if (function_exists('imagecreatefromgif')) $watermark = imagecreatefromgif($watermark_path);
				else return;	
			}
        	elseif ($info[2] == IMAGETYPE_PNG) {
        		if (function_exists('imagecreatefrompng')) $watermark = imagecreatefrompng($watermark_path);
        		else return;
        	}
        	else return;
        	
        	$watermark_padding = 8;
        	
	        $watermark_width = imagesx($watermark);
	        $watermark_height = imagesy($watermark);
	        
	        $image_width = imagesx($image);
	        $image_height = imagesy($image);
	        
        	if ($image_width < $watermark_width*2) return; 
			
        	$watermark_left = $image_width - $watermark_width - $watermark_padding;
        	$watermark_top = $image_height - $watermark_height - $watermark_padding;
        	 
        	imagecopy($image, $watermark, $watermark_left, $watermark_top, 0, 0, $watermark_width, $watermark_height);
		}
		
		protected function getEmptyImage($width, $height, $fill_r=255, $fill_g=255, $fill_b=255) {
	        $ne = imagecreatetruecolor($width, $height);
	        if (!is_null($fill_r) && !is_null($fill_g) && !is_null($fill_b)) {
		        $fill = imagecolorallocate($ne, $fill_r, $fill_g, $fill_b);
		        imagefill($ne, 0, 0, $fill);	        	
	        }
	        return $ne;
		}
		
		
		protected function retainAspectRatio($mode, $w, $h, $neww, $newh) {
			switch ($mode) {
				case 'inscribe':
					if ($w > $neww) $aspect = $w / $neww;
					else $aspect = $h / $newh;
		
					if ($h / $aspect > $newh) $aspect = $h / $newh;
		
					return array(round($w / $aspect), round($h / $aspect));
							
					break;
				case 'crop':
				case 'fit-crop':
					$aspect_h = $h / $newh;
					$aspect_w = $w / $neww;
		    	   	
					$aspect = min($aspect_h, $aspect_w);
		    	
					if ($mode == 'crop' && $aspect < 1) return array($w, $h);
		
					return array(round($w / $aspect), round($h / $aspect));
							
					break;			
			}
		}
		
		
		
		protected function terminate() {  
			header("HTTP/1.0 404 Not Found");
			die();
		}
		
		
	}
	
	
	

    



	