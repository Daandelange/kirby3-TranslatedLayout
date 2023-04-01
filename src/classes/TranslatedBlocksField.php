<?php

use \Kirby\Form\Field\BlocksField;
use \Kirby\Cms\Blueprint;
use \Kirby\Cms\Fieldset;
use \Kirby\Cms\Fieldsets;
use \Kirby\Exception\LogicException;
use \Kirby\Cms\Blocks as BlocksCollection;

require_once( __DIR__ . '/TranslatedBlockTraits.php');

// Class for extending the default layout field to have translateable content with layout structure sync
class TranslatedBlocksField extends BlocksField {
    use TranslatedBlocksTraits;

    public function __construct(array $params = []){
        parent::__construct($params);
    }

    // Extend block mixin
    public function extends(){
        return 'blocks';
    }

    /**
	 * Returns the field type
	 *
	 * @return string
	 */
	public function type(): string {
        // Needs uppercase, see FieldClass.php::type() --> classname is automatically converted from class otherwise, which grabs the wrong component in the panel
		return 'translatedblocks';
	}

    public function store($value){ // Returns the (array) value to store (string). The value has been fill()ed already.
        return parent::store($value);
    }

    // Flattens blocks by placing the ID as key for easier lookup.
    private static function flattenBlocks( BlocksCollection $blocks) : array {
        $flatStructure = [];
        if( !$blocks->isEmpty() ){
            foreach ($blocks->toArray() as $blockIndex => $block){
                // We should have: $block.id , $block.content , $block.type, $block.isHidden
                $keyB = $block['id']??('block_'.$blockIndex);
                if(isset($flatStructure[$keyB]))
                    throw new LogicException("Ouch, now unique IDs can exist twice ! I can't handle this."); // Todo: auto-incremment instead of throwing ? Will not fix translations syncing but can prevent losing block-translations in the content file for manual restore ?
                
                $flatStructure[$keyB] = $block;
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

        // Fetch translation
        $value = $this->flattenBlocks(BlocksCollection::factory(BlocksCollection::parse($value))); // todo: we don't need the BlocksCollection, just the array would be fine ?

        // Check default lang for this model (should always exist anyways)
        $defaultLang = $this->kirby()->defaultLanguage()->code();
        $currentLang = $this->kirby()->language()->code();// $this->model()->translation()->code();// commented is more correct, but loads translation strings = useless here

        $defaultLangTranslation = $this->model()->translation($defaultLang);
        if( !$defaultLangTranslation || !$defaultLangTranslation->exists() ){
            throw new LogicException('Multilanguage is enabled but there is no content for the default language... who\'s the wizzard ?!');
        }

        // Fetch default lang
        $defaultLangValue = BlocksCollection::parse( $defaultLangTranslation->content()[$this->name()] ?? [] );
        $defaultLangBlocks = BlocksCollection::factory( $defaultLangValue, ['parent' => $this->model] )->toArray();

        // Start sanitizing / Syncing the structure

        // Loop blocks and restrict them to the default language
        foreach( $defaultLangBlocks as $blockIndex => &$block){
            $blockID = $block['id']??$blockIndex;
            // Note: If code breaks: Useful inspiration for syncing translations --> ModelWithContent.php [in function content()] :

            // Get blueprint block attribtes
            try {
                $blockBlueprint = $this->fieldset($block['type']);
            } catch (Throwable) {
                // skip invalid block translations
                continue;
            }

            $translateByDefault = true; // todo: parse this from a plugin option ?

            // Translateable and translation available ?
            if(($blockBlueprint->translate() || $translateByDefault) && array_key_exists($blockID, $value) && array_key_exists('content', $value[$blockID]) ){

                // Loop blueprint fields here (not defaultLanguage values) to enable translations not in the default lang ?
                try {
                    $blockFields = $blockBlueprint->fields() ?? []; // todo : Cache fields, like in BlocksField.php::validations()
                } catch (Throwable) {
                    // skip invalid block translations
                    continue;
                }
                foreach($blockFields as $fieldName => $fieldOptions){
                    // Translate if field's translation is explicitly set or if the block is set to translate
                    $translateField = array_key_exists('translate', $fieldOptions) ? ($fieldOptions['translate'] === true) : ($translateByDefault && $blockBlueprint->translate());
                    if(
                        // Is the field translateable ?
                        $translateField

                        // Got keys in both contentTranslations ?
                        && array_key_exists($fieldName, $block['content']) // Note : Useless condition? prevents retrieving translations that don't exist in default lang content ?
                        && array_key_exists($fieldName, $value[$blockID]['content'])
                        // todo: add empty condition on translation ? This brobably should take a blueprint option if translateing empty values. Leaving translations empty can also be useful
                        //&& !V::empty($layouts[$layoutIndex]['columns'][$columnIndex]['blocks'][$blockIndex]['content'])
                    ){
                        // Replace the default lang block content with the translated one.
                        $block['content'][$fieldName] = $value[$blockID]['content'][$fieldName];

                        // Todo : Handle nested fields in a field ? ? ? (structure, etc...)
                    }
                }
            }
        }

        // Reset keys
        $defaultLangBlocks = $this->blocksToValues($defaultLangBlocks);//static::keysToIndexes($defaultLangBlocks);

        // Remember value
        $this->value = $defaultLangBlocks;
    }

    // Override parent routes to intercept pastes
    public function routes(): array {
		$field = $this;
        $parentRoutes = parent::routes();
        foreach($parentRoutes as $key => &$route){
            if($route['pattern']==='paste'){
                $prevAction = $route['action'];
                $route['action'] = function () use ($field, $prevAction) {
                    // Simply disable pasting blocks in translations.
                    if($this->kirby()->language()->isDefault()){
                        try{
                            $newBlocks = $prevAction->call($this, $field);
                        }
                        catch(Throwable $e){
                            throw new Exception('Could not call native route action : '.$e->getMessage());
                        }

                        foreach($newBlocks as $index => &$newBlock){
                            $pastedBlockUuid = $newBlock['id'];
                            // If the same is returned, the panel will generate a new one which will break translations sync
                            // Wrong : The panel always generates a new UUID !
                            $newBlock['id'] = Str::uuid();
                            // Todo : Use $pastedBlockUuid to duplicate it in translations so the paste applies to all langs.
                        }
                        
                        return $newBlocks;
                    }
                    else throw new Exception('Pasting has been disabled in translations, but could be implemented !');
                };
            }
            // todo: modify fieldset api route too
        }
		return $parentRoutes;
	}
}
