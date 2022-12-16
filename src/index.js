
import TranslatedLayoutField from "~/components/TranslatedLayoutField.vue";

panel.plugin("daandelange/translatedlayout", {
  //components: {},

  fields: {
    translatedlayout: TranslatedLayoutField,
    //translatedlayout : {
    //  extends: 'k-layout-field', // <-- works fine without extending anything on js side
    //}
    //translatedblocks: TranslatedBlocksField,
    translatedblocks: {
      extends: 'k-blocks-field', // <-- works fine without extending anything on js side
    },
  },
});
