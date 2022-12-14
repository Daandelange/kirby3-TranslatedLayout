
export default {
    computed: {
        // Editing is only allowed in the default language
        layoutEditingIsDisabled() {
            if(this.disabled==true) return true; // Preserve initial "disabled" behaviour

            // Note: on single lang installations, $language is null --> always allow editing layouts
            if(!this.$root.$language) return false;

            return !this.$root.$language.default;
            //return window.panel.$language.default;
        },
    },
}