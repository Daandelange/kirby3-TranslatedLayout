
import TranslatedLayout from "~/components/TranslatedLayout.vue";

panel.plugin("daandelange/translatedlayout", {
  //components: {},

  fields: {
    translatedLayout: TranslatedLayout,
    // translatedLayout : {
    //   //extends: 'k-layout-field', // <-- works fine without extending anything on js side
    // }
  },
});
