<?php

// Idea: make blocks syncing optional ? (some block have no translations?)

use \Kirby\Form\Field\LayoutField;
use \Kirby\Cms\Layouts;
use \Kirby\Exception\LogicException;

//use \Kirby\Cms\ModelWithContent;
//require_once( __DIR__ . '/TranslatedLayoutFieldContent.php');

// Class for extending the default layout field to have translateable content with layout structure sync
class TranslatedLayoutField extends LayoutField {
    // 'extends' => 'layout', // Build upon blocks

    public function __construct(array $params = []){

        parent::__construct($params);

        // Invert default translate value ?
        //$this->setTranslate(false);//$params['translate'] ?? false);
    }

    public function extends(){
        return 'layout'; // Extend layout mixin
    }

    // Replaces numbered indexes by a string from item[$key].
    public static function indexesToKeys(array $array, string $key='id'): array {
        $ret = [];
        foreach ($array as $layoutKey => $layoutValue) {
            $layoutKey = $layoutValue['id']??$layoutKey;
            $ret[$layoutKey]=$layoutValue;

            if(array_key_exists('columns', $ret[$layoutKey])){
                foreach ($ret[$layoutKey]['columns'] as $columnKey => $columnValue) {
                    unset($ret[$layoutKey]['columns'][$columnKey]);
                    $columnKey = $columnValue['id']??$columnKey;
                    $ret[$layoutKey]['columns'][$columnKey]=$columnValue;

                    if(array_key_exists('blocks', $ret[$layoutKey]['columns'][$columnKey])){
                        foreach ($ret[$layoutKey]['columns'][$columnKey]['blocks'] as $blockKey => $blockValue) {
                            unset($ret[$layoutKey]['columns'][$columnKey]['blocks'][$blockKey]);
                            $blockKey = $blockValue['id']??$blockKey;
                            $ret[$layoutKey]['columns'][$columnKey]['blocks'][$blockKey]=$blockValue;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    // Replaces named keys to numbered indexes. Also syncs back the (previously) named key to item['$key] if it's different.
    public static function keysToIndexes(array $array, string $key='id'): array {
        
        foreach ($array as $layoutKey => $layoutValue) {
            $array[$layoutKey][$key]=$layoutKey; // Sync key with id
            if(array_key_exists('columns', $array[$layoutKey])){
                foreach ($array[$layoutKey]['columns'] as $columnKey => $columnValue) {
                    $array[$layoutKey]['columns'][$columnKey][$key]=$columnKey; // Sync key with id
                    if(array_key_exists('blocks', $array[$layoutKey]['columns'][$columnKey])){
                        foreach ($array[$layoutKey]['columns'][$columnKey]['blocks'] as $blockKey => $blockValue) {
                            $array[$layoutKey]['columns'][$columnKey]['blocks'][$blockKey][$key]=$blockKey; // Sync key with id
                        }
                        $array[$layoutKey]['columns'][$columnKey]['blocks'] = array_values($array[$layoutKey]['columns'][$columnKey]['blocks']); // remove columns keys
                    }
                }
                $array[$layoutKey]['columns'] = array_values($array[$layoutKey]['columns']); // remove columns keys
            }
        }
        $array = array_values($array); // remove keys on level 1
        return $array;
    }
    
    // public function store($value){ // Returns the value to store
        
    //     $value = Layouts::factory($value, ['parent' => $this->model])->toArray();
    //     //dump($value);
    //     //dump($this->layouts);die();
    //     // returns empty string to avoid storing empty array as string `[]`
    //     // and to consistency work with `$field->isEmpty()`
    //     if (empty($value) === true) {
    //         return '';
    //     }

    //     foreach ($value as $layoutIndex => $layout) {
    //         if ($this->settings !== null) {
    //             $value[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->content();
    //         }

    //         foreach ($layout['columns'] as $columnIndex => $column) {
    //             $value[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks'] ?? [], 'content');
    //         }
    //     }

    //     return $this->valueToJson($value, $this->pretty());
    // }

    // Populates the php object with values (used in construct, sve, display, etc) // opposite of store() ? (also used before store  to recall js values)
    public function fill($value = null){

        //Handle return early
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

        // Call parent function
        //parent::fill($value);

        // Fetch translation
        $value   = $this->valueFromJson($value);
        $layouts = Layouts::factory($value, ['parent' => $this->model])->toArray();

        // Check default lang for this model (should always exist anyways)
        $defaultLang = $this->kirby()->defaultLanguage()->code();
        $currentLang = $this->model()->translation()->code();//$this->kirby()->language()->code();
        
        $defaultLangTranslation = $this->model()->translation($defaultLang);
        if( !$defaultLangTranslation || !$defaultLangTranslation->exists() ){
            throw new LogicException('Multilanguage is enabled but there is no content for the default language... who\'s the wizzard ?!');
        }

        // Fetch default lang
        $defaultLangValue = $this->valueFromJson($defaultLangTranslation->content()[$this->name()]);
        $defaultLangLayouts = Layouts::factory($defaultLangValue, ['parent' => $this->model])->toArray();

        $dump = true;
        $dump = false;

        // Start sanitizing / Syncing the structure

        // Note: the functions used in these functions might throw errors in some rare setups
        try{            
            $defaultLangLayouts = static::indexesToKeys($defaultLangLayouts);
            $layouts = static::indexesToKeys($layouts);
        } catch(Throwable $e){
            if($dump) dump('Error somewhere syncing langs : '.$e->getCode().': '.$e->getMessage().' - Line='.$e->getLine().' - File='.$e->getFile());
            return; // <-- todo, this really should not return as it leaves the translation unsynchronized.
        }

        // Filter out layouts that arent in the default language
        // $layouts = array_filter($layouts, function($key) use ($defaultLangLayouts){
        //     //dump("Removing layout ".$key.' !!');
        //     return array_key_exists($key,$defaultLangLayouts);
        // }, ARRAY_FILTER_USE_KEY);

        // Loop the default language's structure and let translation content replace it
        foreach ($defaultLangLayouts as $layoutIndex => $layout) { // <-- Apply blockstovalues

            // Simplified from parent::fill(), not sure if useful, and if so, might need a translation
            // for now, keep behaviour from default language
            if ($this->settings !== null) { // note: original condition... might needs attention ?
                // Sanitize attrs form
                $defaultLangLayouts[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->values();
            }

            foreach($layout['columns'] as $columnIndex => $column) {
                if($dump) dump(' ---newCOLUMN--- '.$columnIndex);

                // Check for entry existing in both langs
                if( false && !array_key_exists($columnIndex, $layouts[$layoutIndex]['columns']) ){
                    if($dump) dump('Warning! --> Column index '.$layoutIndex.'/'.$columnIndex.' doesn\'t exist in the current language! (injecting default language value)');
                    // (Handles automatic default translation for unexisting fields/layouts)
                    //$layouts[$layoutIndex]['columns'][$columnIndex] = $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex];
                    // todo: log ?
                    continue;
                }
                
                // Loop blocks
                foreach( $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'] as $blockIndex => $block){

                    // Note: If code breaks: Useful inspiration for syncing translations --> ModelWithContent.php [in function content()] :

                    // Get blueprint block attribtes
                    $blockBlueprint = $this->fieldset($block['type']);

                    $translateByDefault = true; // todo: parse this from a plugin option ?

                    // Translateable and translation available ?
                    if(($blockBlueprint->translate() || $translateByDefault) && @array_key_exists($blockIndex, $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'])){

                        // Loop blueprint fields here (not defaultLanguage values) to enable translations not in the default lang
                        //foreach($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'] as $fieldName => $fieldData){
                        foreach($blockBlueprint->fields() as $fieldName => $fieldOptions){
                            if(
                                ($fieldOptions['translate']===true || ($fieldOptions['translate']!==false && ($translateByDefault || $blockBlueprint->translate()))) // Translate field, by default or by block translate options
                                // todo: maybe ensure the field isn't translated when explicitly set to false ?
                                && array_key_exists($fieldName, $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'])
                                && array_key_exists($fieldName, $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'])
                                // todo: add empty contidion on translation ?
                            ){
                                //dump('Got a translation !='.$block['type'].'/'.$fieldName);
                                
                                // One way to sync translations
                                $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'][$fieldName]=$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'][$fieldName];

                            }
                        }
                        // Another way, kirby's way, but needs to ensure that keys of the translation are not set, which requires modifying the values on save ideally, but also sanitization here. (todo)
                        //$defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'] = array_merge($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'], $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content']);
                        
                    }

                    if($dump){
                        $blockType = $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['type'];
                        $defaultLangBlockType = $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['type'];
                        dump( $blockType . ' // '. $defaultLangBlockType .' == '. ($blockType==$defaultLangBlockType?'IDENTICAL':'DIFFERENT').' |---| '.$block['content']['text'].' // '.$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content']['text'] );
                    }

                }

                // Compute simplified blueprint to fully expanded options
                //$defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues(array_merge($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'], $layouts[$layoutIndex]['columns'][$columnIndex]['blocks']));
                $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks']);

                // lazy-update/replace ? whole blocks part ? Too buggy in case items get add/removed; only works well when data is a perfect mirror
                //$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = array_combine($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'], array_slice($layouts[$layoutIndex]['columns'][$columnIndex]['blocks']); 
            }
            
        }

        // Reset keys
        $defaultLangLayouts = static::keysToIndexes($defaultLangLayouts);

        // Remember value
        $this->value = $defaultLangLayouts;   
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
