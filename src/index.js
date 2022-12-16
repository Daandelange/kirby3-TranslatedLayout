
import TranslatedLayoutField from "~/components/TranslatedLayoutField.vue";
import TranslatedBlocksField from "~/components/TranslatedBlocksField.vue";
//import TranslatedBlocks from      "~/components/TranslatedBlocks.vue";
import TranslatedBlock from       "~/components/TranslatedBlock.vue";
//import TranslatedLayout from "~/components/TranslatedLayouterLayout.vue"

panel.plugin("daandelange/translatedlayout", {
  components: {
    //'k-layout' : TranslatedLayout, // Not possible, locally-registered component, not globally
    //'k-blocks' : TranslatedBlocks,// Override globally registered k-blocks
    'k-block'  : TranslatedBlock, // Override globally registered k-block
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
