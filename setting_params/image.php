<?php
	
	class imagePkgImageSettingParam extends coreBaseSettingParam {
		
		public function renderField() {			
			$field = coreFormElementsLibrary::get('image', $this->getFieldName());
			
			if (isset($this->constraints['field_params'])) {			
				foreach ($this->constraints['field_params'] as $k=>$v) {
					$setter = coreNameUtilsLibrary::underscoredToCamel("set_$k");					  
					$field->$setter($v);					
				}				
				$field->attr($this->constraints['field_attr']);
			}
			
			$field->setValue($this->param_value);
			$out = $field->render();
			if ($this->param_displayed_unit) $out .= " $this->param_displayed_unit"; 

			return $out;
		}
		
		
		
	}