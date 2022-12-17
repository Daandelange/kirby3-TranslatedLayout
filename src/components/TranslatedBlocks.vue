

<script>

// Overriding the native component is needed in order to:
// - Cancel the add dialog when blocks is empty, (can be triggered by parents).

import TranslatedLayoutMixin from "~/components/TranslatedLayoutMixin.js";
//import KBlocks from "@KirbyPanel/components/Forms/Blocks/Blocks.vue";

export default {
	extends: 'k-blocks',
	name:'k-blocks',
	// components: {
	// 	'k-translated-block-layouts' : TranslatedBlockLayouts,
	// },
	mixins: [
        TranslatedLayoutMixin,
    ],
	props: {
		_devInfo: {
			// Vue-Dev-info, to clarify that this is not the original, for devs debugging with the inspector.
			type: String,
			default: "Warning: I'm not the default k-blocks.\n I have been replaced by a k-translated-blocks !",
		},
	},
	mounted(){
		// Invert functions so ours are called
		// Note : Important to do this on mounted(), beforeCreate and created() both seem too early, some aren't correctly replaced.
		// this.chooseNative = this.choose; this.choose = this.chooseCustom;
		this.invertCustomAndNativeFunctions([
			'choose',
			'addToBatch',
			'onKey',
			'onPaste',
			'paste',
			'pasteboard',
			'append',
			'remove',
			'removeAll',
			'convert',
			'move',
			'copyAll',
			'duplicate',
			'chooseToConvert',
			'add',
			'removeAll',
			'removeSelected',
		]);
	},
	// Cancel some native methods that we don't need ?
	methods: {
		// Helper for replacing native methods on mount.
		// Before: Native functions : myFunc,		Custom functions : myFuncCustom.
		// After : Native functions : myFuncNative,	Custom functions : myFuncCustom & myFunc
		// So we replace the native functions, still being able to call them.
		invertCustomAndNativeFunctions(funcNames){
			for(const fn of funcNames){
				if(this[fn + 'Native']) continue; // if Native is set, this has already been bound
				this[fn + 'Native'] = this[fn]; this[fn] = this[fn + 'Custom'];
			}
		},
		// Never open the choose dialog in translations
		chooseCustom(index){
			return this.layoutEditingIsDisabled ? null : this.chooseNative(index);
		},
		// Never select batches for manipulation
		addToBatchCustom(block){
			return this.layoutEditingIsDisabled ? null : this.addToBatchNative(block);
		},
		// Replace batch-click by normal click (ignore meta key setting the flag)
		onKeyCustom(block, event = null){
			if(this.layoutEditingIsDisabled){
				this.isMultiSelectKey = false;
				return;
			}
			this.onKeyNative(block, event);
		},
		// Ensure that other components can't call paste on this
		onPasteCustom(e){
			if(this.layoutEditingIsDisabled){
				e.preventDefault();
				e.stopImmediatePropagation();
				// Note: returning false lets people paste into the block content. Returning true ignores paste when block selected.
				return false; // Disabled because pasted text is JSON, not useful.
			}
			return this.pasteNative(e);
		},
		// Ignore pasting (also disabled in api, but this prevents a network request)
		async pasteCustom(e) {
			if(this.layoutEditingIsDisabled){
				e.preventDefault();
				e.stopImmediatePropagation();
				// Note: returning false lets people paste into the block content. Returning true ignores paste when block selected.
				return false; // Disabled because pasted text is JSON, not useful.
			}
			return this.pasteNative(e);
		},
		// Never open the pasteboard for pasteing blocks
		pasteboardCustom(){
			return this.layoutEditingIsDisabled ? false : this.pasteboardNative();
		},
		// Ensure append is never called by others or self
		appendCustom(what, index){
			return this.layoutEditingIsDisabled ? null : this.appendNative(what, index);
		},
		// Never remove blocks
		removeCustom(block){
			return this.layoutEditingIsDisabled ? null : this.removeNative(block);
		},
		// Never convert blocks
		async convertCustom(type, block) {
			return this.layoutEditingIsDisabled ? null : this.convertNative(type, block);
		},
		// Never offer to convert
		chooseToConvert(block){
			return this.layoutEditingIsDisabled ? null : this.chooseToConvert(block);
		},
		// Never move blocks
		moveCustom(event){
			return this.layoutEditingIsDisabled ? null : this.moveNative(event);
		},
		// Never copy blocks (todo : maybe some users with to copy translations to somewhere?)
		copyAllCustom(){
			return this.layoutEditingIsDisabled ? null : this.copyAllNative();
		},
		// Never duplicate
		async duplicateCustom(block, index){
			return this.layoutEditingIsDisabled ? null : this.duplicateNative(block, index);
		},
		// Never add blocks
		async addCustom(type = "text", index){
			return this.layoutEditingIsDisabled ? null : this.addNative(type = "text", index);
		},
		// Never removeAll
		removeAllCustom(){
			return this.layoutEditingIsDisabled ? null : this.removeAllNative();
		},
		// Never remove the selected block
		removeSelectedCustom(){
			return this.layoutEditingIsDisabled ? null : this.removeSelectedNative();
		}
	}
}
</script>

<style>

</style>
