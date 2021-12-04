<?php
use Kirby\Form\Field\LayoutField; // maybe not needed ?

require_once(__DIR__ . '/src/classes/TranslatedLayoutField.php');

Kirby::plugin('daandelange/translatedlayout', [
    'fields' => [
        // Undocumented, but identical to kirby's blocks and layout registering
        // See: https://github.com/getkirby/kirby/issues/3961
        'translatedlayout' => 'TranslatedLayoutField',
    ],
    'blueprints' => [
        
        'fields/translatedlayoutwithfieldsetsbis' => __DIR__ . '/src/blueprints/fields/translatedlayoutwithfieldsets.yml',
        'fields/translatedlayoutwithfieldsets' => function ($kirby) {
            // Put all static definitions in an yml file so it's easier to copy/paste/write.
            // From Kirby/Cms/Blueprint.php in function find()

            // Query existing blocks
            $blockBlueprints = $kirby->blueprints('blocks');

            return array_merge(
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
