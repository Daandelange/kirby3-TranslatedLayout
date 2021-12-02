<?php

// Try to make a modelwithcontent bridge to translate layouts and blocks to their fields ???
// Sso we can call $field->toBlocks()->fields()->first()->translation('en') instead of current kirby style :
//                 $field->->translation('en')->toBlocks()->fields()->first() ?
//
// Result : (from my understanding)
// Kirby contant/translations/model management doesn't seem to really bubble as I thought they would, they are more like references to the main root parent passed down to children.
// So they mostly communicate from sub-sub-child to root-parent, but not from parent-to-child-to-child

use \Kirby\Cms\ModelWithContent;
use \Kirby\Exception\LogicException;

// Class for extending the default layout field to have translateable content with layout structure sync
class TranslatedLayoutFieldContent extends ModelWithContent {
    const CLASS_ALIAS = 'TranslatedLayoutFieldContent';

    private ?ModelWithContent $parent = NULL;

    public function __construct(?ModelWithContent $parent){
        if(!$parent) throw new LogicException("TranslatedLayoutFieldContent needs a parent !");
        $this->parent = $parent;
    }

    // Map parent abstract functions to this
    public function contentFileName(): string { return $parent->contentFileName(); }
    public function permissions() { return $parent->permissions(); }
    public function root(): ?string { return $parent->root(); }
    public function blueprint() { return $parent->blueprint(); }
    public function panel() { return $parent->panel(); }
    protected function commit(string $action, array $arguments, Closure $callback) { return $parent->commit($action, $arguments, $callback); }

    // Map any other functions ?
    public function blueprints(string $inSection = null): array                                                                         { return $this->parent->blueprints($inSection); }
    public function content(string $languageCode = null)                                                                                { 
        return $this->parent->content($languageCode);
    }
    public function contentFile(string $languageCode = null, bool $force = false): string                                               { return $this->parent->contentFile($languageCode, $force); }
    public function contentFiles(): array                                                                                               { return $this->parent->contentFiles(); }
    public function contentFileData(array $data, string $languageCode = null): array                                                    { return $this->parent->contentFileData($data, $languageCode); }
    public function contentFileDirectory(): ?string                                                                                     { return $this->parent->contentFileDirectory(); }
    public function contentFileExtension(): string                                                                                      { return $this->parent->contentFileExtension(); }
    public function decrement(string $field, int $by = 1, int $min = 0)                                                                 { return $this->parent->decrement($field, $by, $min); }
    public function errors(): array                                                                                                     { return $this->parent->errors(); }
    public function increment(string $field, int $by = 1, int $max = null)                                                              { return $this->parent->increment(); }
    public function isLocked(): bool                                                                                                    { return $this->parent->isLocked(); }
    public function isValid(): bool                                                                                                     { return $this->parent->isValid(); }
    public function lock()                                                                                                              { return $this->parent->lock(); }
    public function query(string $query = null, string $expect = null)                                                                  { return $this->parent->query($query, $expect); }
    public function readContent(string $languageCode = null): array                                                                     { return $this->parent->readContent($languageCode); }
    public function save(array $data = null, string $languageCode = null, bool $overwrite = false)                                      {
        return $this->parent->save($data, $languageCode, $overwrite); }
    protected function saveContent(array $data = null, bool $overwrite = false)                                                         { return $this->parent->saveContent($data, $overwrite); }
    protected function saveTranslation(array $data = null, string $languageCode = null, bool $overwrite = false)                        { return $this->parent->saveTranslation($data, $languageCode, $overwrite); }
    protected function setContent(array $content = null)                                                                                { return $this->parent->setContent($content); }
    protected function setTranslations(array $translations = null)                                                                      { return $this->parent->setTranslations($translations); }
    public function toSafeString(string $template = null, array $data = [], string $fallback = ''): string                              { return $this->parent->toSafeString($template, $data, $fallback); }
    public function toString(string $template = null, array $data = [], string $fallback = '', string $handler = 'template'): string    { return $this->parent->toString($template, $data, $fallback, $handler); }
    public function translation(string $languageCode = null)                                                                            { return $this->parent->translation($languageCode); }
    public function translations()                                                                                                      { return $this->parent->translations(); }
    public function update(array $input = null, string $languageCode = null, bool $validate = false)                                    { return $this->parent->update($input, $languageCode, $validate); }
    public function writeContent(array $data, string $languageCode = null): bool                                                        { return $this->parent->writeContent($data, $languageCode); }
    public function panelIcon(array $params = null): ?array                                                                             { return $this->parent->panelIcon($params); }
    public function panelImage($settings = null): ?array                                                                                { return $this->parent->panelImage($settings); }
    public function panelOptions(array $unlock = []): array                                                                             { return $this->parent->panelOptions($unlock); }
}
