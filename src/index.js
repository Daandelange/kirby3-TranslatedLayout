
import TranslatedLayoutField from "~/components/TranslatedLayoutField.vue";
import TranslatedBlocksField from "~/components/TranslatedBlocksField.vue";
import TranslatedBlocks from      "~/components/TranslatedBlocks.vue";
import TranslatedBlock from       "~/components/TranslatedBlock.vue";
//import TranslatedLayout from "~/components/TranslatedLayouterLayout.vue"

panel.plugin("daandelange/translatedlayout", {
  components: {
    //'k-layout' : TranslatedLayout, // Not possible, locally-registered component, not globally
    // Important note: There's some kind of recursion replacing the plugin that loads another replaced plugin. First over-ride the child, then the parent container !
    'k-block'  : TranslatedBlock, // Override globally registered k-block
    'k-blocks' : TranslatedBlocks,// Override globally registered k-blocks
    
  },

  fields: {
    translatedlayout: TranslatedLayoutField,
    //translatedlayout : {
    //  extends: 'k-layout-field', // <-- works fine without extending anything on js side
    //}
    translatedblocks: TranslatedBlocksField,
    // translatedblocks: {
    //   extends: 'k-blocks-field', // <-- works fine without extending anything on js side
    // },
  },
});
