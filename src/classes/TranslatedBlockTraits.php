<?php

use \Kirby\Cms\Blueprint;
use \Kirby\Cms\Fieldset;
use \Kirby\Cms\Fieldsets;

// Shared traits for TranslatedBlocksField and TranslatedLayoutField
trait TranslatedBlocksTraits {

    // Override the layout settings blueprint, 
    protected function setSettings($settings = null) {
        // On default lang, use native kirby function, sure not to break.
        if($this->kirby()->language()->isDefault()) return parent::setSettings($settings);// added this line compared to native

		if (empty($settings) === true) {
			$this->settings = null;
			return;
		}

		$settings = Blueprint::extend($settings);
		$settings['icon']   = 'dashboard';
		$settings['type']   = 'layout';
		$settings['parent'] = $this->model();

        // Lines below were added compared to native function
        $settings = $this->adaptFieldsetToTranslation($settings);
        //$settings['disabled'] = true;
        //$settings['editable'] = false; // Adding this line disables saving of attrs/settings ?

		$this->settings = Fieldset::factory($settings);
	}

    // Adds translation statuses to all fields and modifies them according to blueprint.
    private static function adaptFieldsetsToTranslation(array $fieldsets) : array {
        foreach($fieldsets as $key => $fieldset){
            $fieldsets[$key] = static::adaptFieldsetToTranslation($fieldset);
        }
        return $fieldsets;
    }

    private static function adaptFieldsetToTranslation(array $fieldset) : array {
        // Set translations ?
        // Already set via blueprint YML ? if using: "extends: translatedlayoutwithfields" ? Ensure to set defaults ?

        // Set disabed ? Saveable ? if translate is false. So the field is disabled for editing in panel
        if(isset($fieldset['translate']) && $fieldset['translate'] === false ){
            $fieldset['disabled']=true;
            //$fieldset['saveable']=false; // Assumes the field has no value ! Not possible
        }

        return $fieldset;
    }
}
