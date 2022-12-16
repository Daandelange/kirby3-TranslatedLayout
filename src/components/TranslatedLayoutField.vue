<template>
  <!-- Changes : Class items -->
  <k-field
    v-bind="$props"
    :class="{
      'k-translated-layout-field': true,
      'k-layout-field': true,
      'layouts-disabled': layoutEditingIsDisabled
    }"
  >
    <!-- Changes : element name changes, disabled attr -->
    <k-translated-block-layouts
      v-bind="$props"
      @input="$emit('input', $event)"
    />
  </k-field>
</template>

<script>
// This component is a duplicate of components/layouter/Layouts.vue (changes are commented in the template, in case it breaks).
// Purpose: Disable some editing functions but not all, as it is the case with props.disabled

// Notes:
// - BlockLayouts.disabled -> disables layout sidebar but still maintains blocks editable, but no layout settings if they need translations
// - KLayout.disabled -> disables a single row
// - So we need to replace 3 templates just to introduce a new prop, and keep them up to date

import TranslatedBlockLayouts from "~/components/TranslatedLayouterLayouts.vue"
import TranslatedLayoutMixin  from "~/components/TranslatedLayoutMixin.js";

export default {
    extends: 'k-layout-field',
    components: {
        'k-translated-block-layouts' : TranslatedBlockLayouts,
    },
    mixins: [
        TranslatedLayoutMixin,
    ],
};
</script>

<style lang="scss">

// Override Layout css because it's not meant to be disabled
.k-translated-layout-field {

  &.layouts-disabled {
    .k-layout {
      padding: 0; // removes toolbar width on both 
    }

    .k-block-options {
      button.k-block-options-button {
        display: none; // hide all by default
        $editLangs: Edit, Éditer, Bearbeiten, Wijzig; // Todo: more langs need to be in here... the exact kirby translations or 'edit'
        
        @each $translation in $editLangs {
          &[title="#{$translation}"] { // Only option to show... but not multilingual ! !
            display: inherit;
          }
        }

        &:has(.k-icon-edit){ // CSS4 not supported by most browsers...
          display: none;
        }
      }
    }
  }
}
</style>
