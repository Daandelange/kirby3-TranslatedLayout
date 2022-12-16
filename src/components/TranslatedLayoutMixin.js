
export default {
    computed: {

        // Editing is only allowed in the default language
		layoutEditingIsDisabled() {
			// Note: on single lang installations, $language is null --> always allow editing layouts
			if(!this.$root.$language) return false;

			// Is the current language default AND are we child of translated-*-component ?
			return !this.$root.$language.default && this.isWithinTranslatedComponent;
			//return window.panel.$language.default;
		},

        // Helper to figure out if a component is in <k-blocks>/<k-layouts> or rather <k-translated-blocks>/<k-translated-layouts>
		isWithinTranslatedComponent(){
			let tmpParent = this; // Start with self
            const translatedComponents = ['translatedblocks', 'translatedlayout'];
			while( tmpParent != this.$root && tmpParent!=null ){
				if( tmpParent.type && translatedComponents.includes(tmpParent.type) ){
					return true;
				}
				tmpParent = tmpParent.$parent;
			}
			return false;
		},
    },
}