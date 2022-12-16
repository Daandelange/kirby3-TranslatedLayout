<?php

// Idea: make blocks syncing optional ? (some block have no translations?)

use \Kirby\Form\Field\LayoutField;
use \Kirby\Cms\Blueprint;
use \Kirby\Cms\Fieldset;
use \Kirby\Cms\Fieldsets;
use \Kirby\Cms\Layouts;
use \Kirby\Exception\LogicException;

//use \Kirby\Cms\ModelWithContent;
//require_once( __DIR__ . '/TranslatedLayoutFieldContent.php');

// Todo:
// - Add options to configure the behaviour of the field :
//      - Provide a toLayouts() with and without sanitation ? (optimizes: prevent loading default lang in translations by trusting the translation content file )
//      - Provide an option not to save non-translateable duplicate content in the content file.
//      - 
// - Facilitate blueprint setup by providing a way to automatically inject `translate: true|false` to blocks and their fieldset fields.
// - Port the layouts logic to columns and blocks.
// - Miscellaneous improvements :
//      - Double check error handling behaviour
//      - Performance checks
//      - Test suite

// Class for extending the default layout field to have translateable content with layout structure sync
class TranslatedLayoutField extends LayoutField {
    

    public function __construct(array $params = []){
        parent::__construct($params);

        // Invert default translate value ?
        //$this->setTranslate(false);//$params['translate'] ?? false);
    }

    // Extend layout mixin
    // 'extends' => 'layout', // No works...
    public function extends(){
        return 'layout';
    }

    /**
	 * Returns the field type
	 *
	 * @return string
	 */
	public function type(): string {
        // Needs uppercase, see FieldClass.php::type() --> classname is automatically converted from class otherwise, which grabs the wrong component in the panel
		return 'translatedlayout';
	}

    // public function props() : array { // from/in-sync-with the blueprint
    //     return array_merge(parent::props(), [
	// 		//'empty'          => $this->empty(),
    //         //'translate' => false,
    //         //'disabled' => true, // Disabled state for the layouts field, disables adding/removing layouts. BUT disables all contained blocks too. Needs to be modified within the field component.
    //         //'type' => 'translatedlayout', // dunno why, php sets it to translatedLayout....
	// 	]);
    // }

    // // Replaces numbered indexes by a string from item[$key].
    // public static function indexesToKeys(array $array, string $key='id'): array {
    //     $ret = [];
    //     foreach ($array as $layoutKey => $layoutValue) {
    //         $layoutKey = $layoutValue['id']??$layoutKey;
    //         $ret[$layoutKey]=$layoutValue;

    //         if(array_key_exists('columns', $ret[$layoutKey])){
    //             foreach ($ret[$layoutKey]['columns'] as $columnKey => $columnValue) {
    //                 unset($ret[$layoutKey]['columns'][$columnKey]);
    //                 $columnKey = $columnValue['id']??$columnKey;
    //                 $ret[$layoutKey]['columns'][$columnKey]=$columnValue;

    //                 if(array_key_exists('blocks', $ret[$layoutKey]['columns'][$columnKey])){
    //                     foreach ($ret[$layoutKey]['columns'][$columnKey]['blocks'] as $blockKey => $blockValue) {
    //                         unset($ret[$layoutKey]['columns'][$columnKey]['blocks'][$blockKey]);
    //                         $blockKey = $blockValue['id']??$blockKey;
    //                         $ret[$layoutKey]['columns'][$columnKey]['blocks'][$blockKey]=$blockValue;
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $ret;
    // }

    // // Replaces named keys to numbered indexes.
    // public static function keysToIndexes(array $array, string $key='id'): array {

    //     foreach ($array as $layoutKey => $layoutValue) {
    //         //$array[$layoutKey][$key]=$layoutKey; // Sync key with id
    //         if(array_key_exists('columns', $array[$layoutKey])){
    //             foreach ($array[$layoutKey]['columns'] as $columnKey => $columnValue) {
    //                 //$array[$layoutKey]['columns'][$columnKey][$key]=$columnKey; // Sync key with id
    //                 if(array_key_exists('blocks', $array[$layoutKey]['columns'][$columnKey])){
    //                     foreach ($array[$layoutKey]['columns'][$columnKey]['blocks'] as $blockKey => $blockValue) {
    //                         //$array[$layoutKey]['columns'][$columnKey]['blocks'][$blockKey][$key]=$blockKey; // Sync key with id
    //                     }
    //                     $array[$layoutKey]['columns'][$columnKey]['blocks'] = array_values($array[$layoutKey]['columns'][$columnKey]['blocks']); // remove blocks keys
    //                 }
    //             }
    //             $array[$layoutKey]['columns'] = array_values($array[$layoutKey]['columns']); // remove columns keys
    //         }
    //     }
    //     $array = array_values($array); // remove keys on level 1
    //     return $array;
    // }

    public function store($value){ // Returns the (array) value to store (string). The value has been fill()ed already.
        return parent::store($value);
    }

    // Flattens a layout. All blocks, columns and layouts are in their own array. 
    private static function flattenLayoutsColumnsBlocks( Kirby\Cms\Layouts $layouts/*, array $columns = ['layouts','columns','blocks']*/ ) : array {
        $flatStructure = ['layouts'=>[],/*'columns'=>[],*/'blocks'=>[]];
        if( !$layouts->isEmpty() ){
            foreach ($layouts->toArray() as $layoutIndex => $layout) {
                // We should have: $layout.id , $layout.columns , $layout.attrs
                if( isset($layout['columns']) ){
                    foreach($layout['columns'] as $columnIndex => $column) {
                        // We should have: $column.id , $column.blocks , $column.width
                        if( isset($column['blocks']) ){
                            foreach( $column['blocks'] as $blockIndex => $block){
                                // We should have: $block.id , $block.content , $block.type, $block.isHidden
                                $keyB = $block['id']??$blockIndex;
                                if(isset($flatStructure['blocks'][$keyB]))
                                    throw new LogicException("Ouch, now unique IDs can exist twice ! I can't handle this.");
                                
                                $flatStructure['blocks'][$keyB] = $block;
                            }
                            unset($column['blocks']);
                        }
                        // $keyC = $column['id']??$columnIndex;
                        // $flatStructure['columns'][$keyC] = $column;
                    }
                    unset($layout['columns']);
                }
                $keyL = $layout['id']??$layoutIndex;
                if(isset($flatStructure['blocks'][$keyL]))
                    throw new LogicException("Ouch, now unique IDs can exist twice ! I can't handle this.");
                $flatStructure['layouts'][$keyL] = $layout; // Note: Attrs are simply copied within
            }
        }
        return $flatStructure;
    }

    // Value setter (used in construct, save, display, etc) // opposite of store() ? (also used before store  to recall js values)
    // Note : Panel.page.save passes an array while loadFromContent passes a yaml string.
    public function fill($value = null){

        // Default lang uses native kirby code, which is faster. :)
        if(
            // Single lang has normal behaviour
            ( $this->kirby()->multilang() === false ) ||
            // Default lang has normal behaviour.
            ( $this->model()->translation()->isDefault() ) ||
            // if attrs.translate is set to false
            ( $this->translate() === false )
        ){
            return parent::fill($value);
        }
        
        // <!-- begin original code (with comments added) ---
        // String to array
        // $value   = $this->valueFromJson($value); // <-- parses json
        // // Restricts values to blueprint settings (sanitizes and returns constructed objects)
        // $layouts = Layouts::factory($value, ['parent' => $this->model])->toArray();
        // foreach ($layouts as $layoutIndex => $layout) { // <-- Apply blockstovalues
        //     if ($this->settings !== null) {
        //         // Sanitize attrs form
        //         $layouts[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->values();
        //     }

        //     foreach ($layout['columns'] as $columnIndex => $column) {
        //         // Sanitizes blocks
        //         //$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks']);
        //     }
        // }
        //$this->value = $layouts;
        // <!-- end original code --->

        // Fetch translation

        // OPTION A : We got an array, the panels sends us a full layout that we have to parse, probably for saveing it
        if( is_array($value) ){
            
            // Secure
            if( !empty($value) && ( !isset($value[0]) || !isset($value[0]['columns']) || !isset($value[0]['id']) ) ){
                // Todo: on save, the value become null when throwing, which sets the stored value to null. The panel doesn't notify anything.
                // Maybe: Rather die and respond with a panel error if  Or is there a way to handle this response natively ?
                throw new LogicException('The layout field received an unfamiliar array format, throwing to ensure everything is OK.');
            }

            // Keep flattened
            $value = $this->flattenLayoutsColumnsBlocks( Layouts::factory($value, ['parent' => $this->model]) );
        }
        // OPTION B : We got a string, the value comes from the content file which only stores the translations
        elseif( is_string($value) ){
            // Parse to array
            $value = $this->valueFromJson($value); // Ensures the value is an array

            // Convert from native translation storage format (copy-pasted or saved in pre v0.3.0 : data was stored like default lang, with the layouts and all)
            if( isset($value[0]) && isset($value[0]['columns']) && isset($value[0]['id']) ){ // Value is same as when getting a panel save
                $value = $this->flattenLayoutsColumnsBlocks( Layouts::factory($value, ['parent' => $this->model]) );
            }
            // Check values ?
            if( !isset($value['layouts']) || !isset($value['blocks']) )
                throw new LogicException('The parsed string data looks wrong. Aborting.');
        }
        // OPTION C : Huh? Is there an option C ?!
        else {
            throw new LogicException('Could not parse the layout value !');
        }

        // Todo : after some testing, the logic exceptions above and below could return the default language, just in case...

        // Check default lang for this model (should always exist anyways)
        $defaultLang = $this->kirby()->defaultLanguage()->code();
        $currentLang = $this->kirby()->language()->code();// $this->model()->translation()->code();// commented is more correct, but loads translation strings = useless here

        $defaultLangTranslation = $this->model()->translation($defaultLang);
        if( !$defaultLangTranslation || !$defaultLangTranslation->exists() ){
            throw new LogicException('Multilanguage is enabled but there is no content for the default language... who\'s the wizzard ?!');
        }

        // Fetch default lang
        $defaultLangValue = $this->valueFromJson( $defaultLangTranslation->content()[$this->name()] ?? [] );
        $defaultLangLayouts = Layouts::factory($defaultLangValue, ['parent' => $this->model])->toArray();

        // Start sanitizing / Syncing the structure

        // Loop the default language's structure and let translation content replace it
        foreach ($defaultLangLayouts as $layoutIndex => &$layout) { // <-- Apply blockstovalues
            $layoutID = $layout['id']??$layoutIndex;

            // Check the layout settings / attrs
            if ($this->settings !== null) { // 
                // Generate the corresponding form
                $attrForm = $this->attrsForm($layout['attrs']);

                // Load value from default lang
                $layout['attrs'] = $attrForm->values();

                // Check for translations
                $attrFields = $attrForm->fields();
                if( $attrFields->count() > 0 && isset($value['layouts'][$layoutID]) && array_key_exists('attrs', $value['layouts'][$layoutID]) ){

                    // Loop default attrs by field
                    foreach($attrFields as $fieldName => $attrField){
                        // Translate if needed
                        if(
                            $attrField->translate() === true // the field translates
                            && isset($value['layouts'][$layoutID]['attrs'][$fieldName]) // The translation exists
                        ){
                            $layout['attrs'][$fieldName] = $value['layouts'][$layoutID]['attrs'][$fieldName];
                            // Todo : What if translation is empty ?
                            // !V::empty($layouts[$layoutIndex]['attrs'][$attrIndex])

                            // Todo: also handle nested fields translation ?
                        }
                    }
                }
            }

            foreach($layout['columns'] as $columnIndex => &$column) {
                $columnID = $column['id']??$columnIndex;

                // Loop blocks and restrict them to the default language
                foreach( $column['blocks'] as $blockIndex => &$block){
                    $blockID = $block['id']??$blockIndex;
                    // Note: If code breaks: Useful inspiration for syncing translations --> ModelWithContent.php [in function content()] :

                    // Get blueprint block attribtes
                    $blockBlueprint = $this->fieldset($block['type']);

                    $translateByDefault = true; // todo: parse this from a plugin option ?

                    // Translateable and translation available ?
                    if(($blockBlueprint->translate() || $translateByDefault) && array_key_exists($blockID, $value['blocks'])){
                        // Loop blueprint fields here (not defaultLanguage values) to enable translations not in the default lang
                        //foreach($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'] as $fieldName => $fieldData){
                        foreach($blockBlueprint->fields() as $fieldName => $fieldOptions){
                            // Translate if field's translation is explicitly set or if the block is set to translate
                            $translateField = array_key_exists('translate', $fieldOptions) ? ($fieldOptions['translate'] === true) : ($translateByDefault && $blockBlueprint->translate());
                            if(
                                // Is the field translateable ?
                                $translateField

                                // Got keys in both contentTranslations ?
                                && array_key_exists($fieldName, $block['content'])
                                && array_key_exists($fieldName, $value['blocks'][$blockID]['content'])
                                // todo: add empty condition on translation ? This brobably should take a blueprint option if translateing empty values. Leaving translations empty can also be useful
                                //&& !V::empty($layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'])
                            ){
                                //dump('Got a translation !='.$block['type'].'/'.$fieldName);
                                
                                // Replace the default lang block content with the translated one.
                                $block['content'][$fieldName]=$value['blocks'][$blockID]['content'][$fieldName];

                            }
                            // Todo : Handle nested fields in a field ? ? ? (structure, etc...)

                            // Todo: the fields loop can be heavy to loop, maybe unset the field once used, to speed up the next iterations ?
                        }
                        // Alternative way, kirby's way, but needs to ensure that keys of the translation are not set, which requires modifying the values on save ideally, but also sanitization here. (todo)
                        //$defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'] = array_merge($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'], $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content']);

                        // Fallback when a block has no fields ? Are there blocks with content AND without fields ?
                    }

                }

                // Compute simplified blueprint to fully expanded options (like original Kirby fill() function)
                //$defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues(array_merge($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'], $layouts[$layoutIndex]['columns'][$columnIndex]['blocks']));
                $column['blocks'] = $this->blocksToValues($column['blocks']);

                // lazy-update/replace ? whole blocks part ? Too buggy in case items get add/removed; only works well when data is a perfect mirror. Also, array_combine tends to be quite slow.
                //$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = array_combine($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'], array_slice($layouts[$layoutIndex]['columns'][$columnIndex]['blocks']);
            }

        }

        // Reset keys
        //$defaultLangLayouts = static::keysToIndexes($defaultLangLayouts);

        // Remember value
        $this->value = $defaultLangLayouts;
    }

    // Override fieldsets for translations. Fieldsets define block blueprints, which allow controlling their translation status.
    protected function setFieldsets($fieldsets, $model) {
        // On default lang, use native kirby function, sure not to break.
        if($this->kirby()->language()->isDefault()) return parent::setFieldsets($fieldsets, $model);// added this line compared to native

		if (is_string($fieldsets) === true) {
			$fieldsets = [];
		}

        $fieldsets = $this->adaptFieldsetsToTranslation($fieldsets);// added this line compared to native

		$this->fieldsets = Fieldsets::factory($fieldsets, [
			'parent' => $model
		]);
	}

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

    // Try to override these ModelWithContent methods
    //public function translation(string $languageCode = null) { return $this->parent->translation($languageCode); }
    //public function translations();

    // Check if this function is called ?
    // protected function i18n($param = null): ?string
    // {
    //     return empty($param) === false ? I18n::translate($param, $param) : null;
    // }
}
