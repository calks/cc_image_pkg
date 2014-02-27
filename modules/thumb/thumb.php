<?php
error_reporting(E_ALL); ini_set('display_errors',1);
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
			//echo $this->source_path;

			if (!is_file($this->source_path)) return $this->terminate();
			
			$thumb_filename = @array_shift($params);
			
			if (!$this->parseImageParams($thumb_filename)) return $this->terminate();
			
			$destination_path = $this->getDestinationPath();			
			
			$info = getimagesize($this->source_path);
			if ($info[2] == IMAGETYPE_JPEG) {
				if (function_exists('imagecreatefromjpeg')) $img = imagecreatefromjpeg($this->source_path);
				else return false;
			} elseif ($info[2] == IMAGETYPE_GIF) {
				$img = imagecreatefromgif($this->source_path);	
			}
        	elseif ($info[2] == IMAGETYPE_PNG) {
        		$img = imagecreatefrompng($this->source_path);
        	}
        	else return $this->terminate();
        	
        	$src_width = $info[0];
        	$src_height = $info[1];

                
			if ($this->mode == 'crop') { 
		        $dims = $this->retainAspectRationCrop($src_width, $src_height, $this->width, $this->height);

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
		        if ($info[0] > $this->width || $info[1] > $this->height) $dims = $this->retainAspectRation($src_width, $src_height, $this->width, $this->height);
		        else $dims = array($info[0], $info[1]);
		        $srcLeft = 0;
		        $srcTop = 0;
		        $destLeft = 0;
		        $destTop = 0;
		        $out_width = $dims[0];
		        $out_height = $dims[1];
		        $dest_width = $dims[0];
		        $dest_height = $dims[1];
		        
	        }

	        $ne = imagecreatetruecolor($out_width, $out_height);
	        $white = imagecolorallocate($ne , 255, 255, 255);
	        imagefill($ne, 0, 0, $white);

        	imagecopyresampled($ne, $img, $destLeft, $destTop, $srcLeft, $srcTop, $dest_width, $dest_height, $info[0], $info[1]);

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

        	call_user_func($func, $ne, $destination_path);

        	if (is_file($destination_path)) $this->redirectToImage();
        	else return $this->terminate();        	
		}
		
		
		protected function retainAspectRation($w, $h, $neww, $newh) {

			if ($w > $neww) $aspect = $w / $neww;
			else $aspect = $h / $newh;

			if ($h / $aspect > $newh) $aspect = $h / $newh;

			return array(round($w / $aspect), round($h / $aspect));
		}
    
    
		protected function retainAspectRationCrop($w, $h, $neww, $newh) {

			$aspect_h = $h / $newh;
			$aspect_w = $w / $neww;
    	   	
			$aspect = ($aspect_h<$aspect_w) ? $aspect_h : $aspect_w;
    	
			if ($aspect < 1) return array($w, $h);

			return array(round($w / $aspect), round($h / $aspect));
		}
		
		
		
		protected function terminate() {  
			header("HTTP/1.0 404 Not Found");
			die();
		}
		
		
	}
	
	
	

    



	