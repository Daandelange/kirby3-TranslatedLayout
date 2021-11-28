<template>
  <k-field
    v-bind="$props"
    :class="{
      'k-translated-layout-field': true,
      'k-layout-field': true,
      'layouts-disabled': layoutEditingIsDisabled
    }"
  >
    <k-block-layouts
      v-bind="$props"
      @input="$emit('input', $event)"
      :disabled="layoutEditingIsDisabled"
    />
  </k-field>
</template>

<script>
// Notes:
// - Blockfield.disabled -> disables layout sidebar but still maintains blocks editable
// - KLayout.disabled -> disables a single row
export default {
  //extends: 'layout',
  extends: 'k-layout-field',
  computed: {
    // Editing is only allowed in the default language
    layoutEditingIsDisabled() {
      // Note: on single lang installations, $language is null --> always allow editing layouts
      if(!this.$root.$language) return false;

      return !this.$root.$language.default;
      //return window.panel.$language.default;
    },
  }
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
