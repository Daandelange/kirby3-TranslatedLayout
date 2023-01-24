<?php
use \Kirby\Form\Field\LayoutField; // maybe not needed ?

require_once(__DIR__ . '/src/classes/TranslatedLayoutField.php');
require_once(__DIR__ . '/src/classes/TranslatedBlocksField.php');

Kirby::plugin('daandelange/translatedlayout', [
    'fields' => [
        // Undocumented, but identical to kirby's blocks and layout registering
        // Strings are checked against classes then instanciated.
        // See: https://github.com/getkirby/kirby/issues/3961
        'translatedlayout' => 'TranslatedLayoutField',
        'translatedblocks' => 'TranslatedBlocksField',
    ],
    'blueprints' => [

        // Todo: Possible issue = when these blocks are not registered in the user blueprint, they get added. NVM they are just defaults.
        'fields/translatedlayoutwithfieldsetsbis' => __DIR__ . '/src/blueprints/fields/translatedlayoutwithfieldsets.yml',
        'fields/translatedlayoutwithfieldsets' => function ($kirby) { // Todo: rename this to translatedlayoutwithfieldsettranslations
            // Put all static definitions in an yml file so it's easier to copy/paste/write.
            // From Kirby/Cms/Blueprint.php in function find()

            // Query existing blocks
            $blockBlueprints = $kirby->blueprints('blocks');

            return array_merge(

                // Load static properties from file
                Data::read( __DIR__ . '/src/blueprints/fields/translatedlayoutwithfieldsets.yml' ),

                // Dynamically inject non-default blocks depending on installed addons
                // Todo: add more translation settings for community blocks

                //
                (in_array('woo/localvideo', $blockBlueprints) ? [
                    'translate' => false,
                    'tabs'  => [
                        'source' => [
                            'fields' => [
                                'vidfile' => [
                                    'translate' => false,
                                ],
                                'vidposter' => [
                                    'translate' => false,
                                ],
                            ],
                        ],
                        'settings' => [
                            'fields' => [
                                'class' => [
                                    'translate' => false,
                                ],
                                'controls' => [
                                    'translate' => false,
                                ],
                                'mute' => [
                                    'translate' => false,
                                ],
                                'autoplay' => [
                                    'translate' => false,
                                ],
                                'loop' => [
                                    'translate' => false,
                                ],
                                'playsinline' => [
                                    'translate' => false,
                                ],
                                'preload' => [
                                    'translate' => false,
                                ],
                            ],
                        ],
                    ]
                ] : [])
            );
        }
    ],
]);
