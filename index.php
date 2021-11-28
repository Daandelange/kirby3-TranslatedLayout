<?php
use Kirby\Form\Field\LayoutField; // maybe not needed ?

require_once(__DIR__ . '/src/classes/TranslatedLayoutField.php');

Kirby::plugin('daandelange/translatedlayout', [
    'fields' => [
        // Undocumented, but identical to kirby's blocks and layout registering
        // See: https://github.com/getkirby/kirby/issues/3961
        'translatedlayout' => 'TranslatedLayoutField',
    ],
]);
