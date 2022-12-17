<?php

use \Kirby\Cms\Blueprint;
use \Kirby\Cms\Fieldset;
use \Kirby\Cms\Fieldsets;

// Shared traits for TranslatedBlocksField and TranslatedLayoutField
trait TranslatedBlocksTraits {

    // Override fieldsets for translations. Fieldsets define block blueprints, which allow controlling their translation status.
    protected function setFieldsets($fieldsets, $model) {

        // On default lang, use native kirby function, sure not to break.
        if($model->kirby()->language()->isDefault()) return parent::setFieldsets($fieldsets, $model);// added this line compared to native

        if (is_string($fieldsets) === true) {
            $fieldsets = [];
        }

        $fieldsets = $this->adaptFieldsetsToTranslation($fieldsets);// added this line compared to native

        // Todo : if fieldsets is null,  factory() seems to set it to a default set, causing disabled not to be set correctly...
        $this->fieldsets = Fieldsets::factory($fieldsets, [
            'parent' => $model
        ]);
    }    

    // Adds translation statuses to all fields and modifies them according to blueprint.
    private static function adaptFieldsetsToTranslation(?array $fieldsets) : ?array {
        if($fieldsets) foreach($fieldsets as $key => &$fieldset){
            $fieldset = static::adaptFieldsetToTranslation($fieldset);

            // Todo: can it happen that groups contain more fieldsets ? They might need a dedicated if()...
        }
        return $fieldsets;
    }

    private static function adaptFieldsetToTranslation(?array $fieldset) : ?array {
        // Set translations ?
        // Already set via blueprint YML ? if using: "extends: translatedlayoutwithfields" ? Ensure to set defaults ?

        // Set disabed ? Saveable ? if translate is false. So the field is disabled for editing in panel
        if($fieldset && isset($fieldset['translate']) && $fieldset['translate'] === false ){
            $fieldset['disabled']=true;
            //$fieldset['saveable']=false; // Assumes the field has no value ! Not possible
        }

        return $fieldset;
    }
}
