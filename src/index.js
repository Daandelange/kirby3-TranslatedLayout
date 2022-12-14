
import TranslatedLayout from "~/components/TranslatedLayout.vue";

panel.plugin("daandelange/translatedlayout", {
  //components: {},

  fields: {
    translatedlayout: TranslatedLayout,
    //translatedlayout : {
    //  extends: 'k-layout-field', // <-- works fine without extending anything on js side
    //}
  },
});
