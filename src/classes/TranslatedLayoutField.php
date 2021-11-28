<?php

// Idea: make blocks syncing optional ? (some block have no translations?)

use \Kirby\Form\Field\LayoutField;
use \Kirby\Cms\Layouts;
use \Kirby\Exception\LogicException;

// Class for extending the default layout field to have translateable content with layout structure sync
class TranslatedLayoutField extends LayoutField {
    //     'extends' => 'layout', // Build upon blocks

    public function __construct(array $params = []){
        parent::__construct($params);
        // Invert default translate value
        //$this->setTranslate(false);//$params['translate'] ?? false);
    }

    public function extends(){
        return 'layout'; // Extend layout mixin
    }

    // public function props() : array { // from/in-sync-with the blueprint
    //     // return array_merge(parent::props(), [
    //     //     'translate' => false,
    //     // ]);
    //     return [
    //         //'empty'          => $this->empty(),
    //         'translate' => false,
    //     ] + parent::props();
    // }

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

        // <-- begin original code (with comments added) ---
        // // String to array
        // $value   = $this->valueFromJson($value);
        // // Restricts values to blueprint settings (sanitizes)
        // $layouts = Layouts::factory($value, ['parent' => $this->model])->toArray();
        // foreach ($layouts as $layoutIndex => $layout) {
        //     if ($this->settings !== null) {
        //         // Sanitize attrs form
        //         $layouts[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->values();
        //     }

        //     foreach ($layout['columns'] as $columnIndex => $column) {
        //         // Sanitizes blocks
        //         $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks']);
        //     }
        // }
        // $this->value = $layouts;
        // -- end original code --->

        // Call parent function
        parent::fill($value);

        // Single lang has normal behaviour
        if( $this->kirby()->multilang() === false ) return;

        // Default lang has normal behaviour.
        // Todo: adapt other langs? Or at least touch() their files / flush cache ? For now they are modified on next parse.
        if( $this->model()->translation()->isDefault() ) return;
        
        $layouts = $this->value;

        // Check default lang for this model (should always exist anyways)
        $defaultLang = $this->kirby()->defaultLanguage()->code();
        $currentLang = $this->model()->translation()->code();//$this->kirby()->language()->code();

        
        $defaultLangTranslation = $this->model()->translation($defaultLang);
        if( !$defaultLangTranslation || !$defaultLangTranslation->exists() ){
            throw new LogicException('Multilanguage is enabled but there is not content for the default language... who\'s the wizzard ?!');
        }

        // Parse default lang layout
        $fieldName = $this->name();
        $defaultLangValue = $this->valueFromJson($defaultLangTranslation->content()[$fieldName]);
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
        $layouts = array_filter($layouts, function($key) use ($defaultLangLayouts){
            //dump("Removing layout ".$key.' !!');
            return array_key_exists($key,$defaultLangLayouts);
        }, ARRAY_FILTER_USE_KEY);


        foreach($defaultLangLayouts as $layoutIndex => $layout) {
            
            if($dump) dump(' ===newROW   === '.$layoutIndex);

            // Check for line existing in both langs
            if( !array_key_exists($layoutIndex, $layouts) ){
                if($dump) dump('Warning! --> Layout index '.$layoutIndex.' doesn\'t exist in the current language! (injecting default language value)');

                // (Handles automatic default translation for unexisting fields/layouts)
                $layouts[$layoutIndex] = $defaultLangLayouts[$layoutIndex];

                // todo: log ?
                continue;
            }

            // Check if they hold too much values (for array_merge and removing unneccesary keys)
            // Not really needed as long as the panel has been used since the beginning. Otherwise this could solve some weird data-loss scenarios.
            // if( count($defaultLangLayouts[$layoutIndex]['columns']) < count($layouts[$layoutIndex]['columns']) ){
            //     if($dump) dump('Warning! --> There are some additional columns in the translated version of layout '.$layoutIndex.'. Removing extra columns. (sync might break content)');
            //     $layouts[$layoutIndex]['columns'] = array_slice($layouts[$layoutIndex]['columns'], 0, count($defaultLangLayouts[$layoutIndex]['columns']), true);
            //     if($dump) dump('New number of columns = '.count($layouts[$layoutIndex]['columns']));
            //     // todo: log this ?
            // }
            
            if ($this->settings !== null) { // note: original condition... might needs attention ?
                // Sanitize attrs form
                //$defaultLangLayouts[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->values();
            }

            // Filter out columns that arent in the default language
            $layouts[$layoutIndex]['columns'] = array_filter(
                $layouts[$layoutIndex]['columns'],
                function($key) use ($defaultLangLayouts, $layoutIndex){
                    //if(!array_key_exists($key,$defaultLangLayouts[$layoutIndex]['columns'])) dump("Removing column ".$key.' !!');
                    return array_key_exists($key,$defaultLangLayouts[$layoutIndex]['columns']);
                },
                ARRAY_FILTER_USE_KEY
            );

            foreach($layout['columns'] as $columnIndex => $column) {
                if($dump) dump(' ---newCOLUMN--- '.$columnIndex);

                // Check for entry existing in both langs
                if( !array_key_exists($columnIndex, $layouts[$layoutIndex]['columns']) ){
                    if($dump) dump('Warning! --> Column index '.$layoutIndex.'/'.$columnIndex.' doesn\'t exist in the current language! (injecting default language value)');
                    // (Handles automatic default translation for unexisting fields/layouts)
                    $layouts[$layoutIndex]['columns'][$columnIndex] = $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex];
                    // todo: log ?
                    continue;
                }

                // Filter out blocks that arent in the default language
                // Todo: could be set to empty array, then just populate it ?
                $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = array_filter(
                    $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'],
                    function($key) use ($defaultLangLayouts, $layoutIndex, $columnIndex, $layouts){
                        return array_key_exists($key,$defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks']);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                
                foreach( $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'] as $blockIndex => $block){
                    if($dump) dump(' ---newBLOCK --- '.$blockIndex);

                    // Check for line existing in both langs
                    if( !array_key_exists($blockIndex, $layouts[$layoutIndex]['columns'][$columnIndex]['blocks']) ){
                        if($dump) dump('Warning! --> Block index '.$layoutIndex.'/'.$columnIndex.'/'.$blockIndex.' doesn\'t exist in the current language! (injecting default language value)');
                        // (Handles automatic default translation for unexisting fields/layouts)
                        $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex] = $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex];
                        // todo: log ?
                        continue;
                    }

                    //$block['content'][$fieldType]=$value; // <-- Structure
                    // $block['id'], $block['isHidden'], $block['type']=heading/text/etc (blocktypes)
                    //dump( $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex] );
                    //dump( $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex] );
                    
                    // Sync fields of block ? (untranslateablefields might not need to store a translation?)
                    // foreach($){
                    // }

                    if($dump){
                        $blockType = $layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['type'];
                        $defaultLangBlockType = $defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['type'];
                        dump( $blockType . ' // '. $defaultLangBlockType .' == '. ($blockType==$defaultLangBlockType?'IDENTICAL':'DIFFERENT').' |---| '.$block['content']['text'].' // '.$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content']['text'] );
                    }

                }

                // lazy-update/replace ? whole blocks part ? Too buggy in case items get add/removed; only works well when data is a perfect mirror
                //$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = array_combine($defaultLangLayouts[$layoutIndex]['columns'][$columnIndex]['blocks'], array_slice($layouts[$layoutIndex]['columns'][$columnIndex]['blocks']); 
            }
            
        }

        if($dump) dump($layouts);
        if($dump) dump('ENDLAYOUTS');

        // Reset keys
        $layouts = static::keysToIndexes($layouts);

        // Remember value
        $this->value = $layouts;


        if($dump) die();        
    }

    // Check if this function is called ?
    // protected function i18n($param = null): ?string
    // {
    //     return empty($param) === false ? I18n::translate($param, $param) : null;
    // }
}
