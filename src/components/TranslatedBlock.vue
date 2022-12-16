<template>
	<div
		ref="container"
		:class="'k-block-container-type-' + type"
		:data-batched="isBatched"
		:data-disabled="fieldset.disabled"
		:data-hidden="isHidden"
		:data-id="id"
		:data-last-in-batch="isLastInBatch"
		:data-selected="isSelected"
		:data-translate="fieldset.translate"
		class="k-block-container"
		tabindex="0"
		@keydown.ctrl.shift.down.prevent="$emit('sortDown')"
		@keydown.ctrl.shift.up.prevent="$emit('sortUp')"
		@focus="$emit('focus')"
		@focusin="onFocusIn"
	>
		<div :class="className" class="k-block">
			<component
				:is="customComponent"
				ref="editor"
				v-bind="$props"
				v-on="listeners"
			/>
		</div>

		<!-- new block compared to native template -->
		<k-dropdown class="k-block-options" v-if="layoutEditingIsDisabled && isEditable">
			<k-button
				v-if="isEditable"
				:tooltip="$t('edit')"
				icon="edit"
				class="k-block-options-button"
				@click="open"
			/>
		</k-dropdown>
		<k-block-options v-else
			ref="options"
			:is-batched="isBatched"
			:is-editable="isEditable"
			:is-full="isFull"
			:is-hidden="isHidden"
			v-on="listeners"
		/><!-- new v-else compared to native template -->

		<k-form-drawer
			v-if="isEditable && !isBatched"
			:id="id"
			ref="drawer"
			:icon="fieldset.icon || 'box'"
			:tabs="tabs"
			:title="fieldset.name"
			:value="content"
			class="k-block-drawer"
			@close="focus()"
			@input="$emit('update', $event)"
		>
			<template #options>
				<k-button
					v-if="isHidden"
					class="k-drawer-option"
					icon="hidden"
					@click="$emit('show')"
				/>
				<k-button
					:disabled="!prev"
					class="k-drawer-option"
					icon="angle-left"
					@click.prevent.stop="goTo(prev)"
				/>
				<k-button
					:disabled="!next"
					class="k-drawer-option"
					icon="angle-right"
					@click.prevent.stop="goTo(next)"
				/>
				<k-button
					class="k-drawer-option"
					icon="trash"
					@click.prevent.stop="confirmToRemove"
				/>
			</template>
		</k-form-drawer>

		<k-remove-dialog
			ref="removeDialog"
			:text="$t('field.blocks.delete.confirm')"
			@submit="remove"
		/>
	</div>
</template>

<script>

import TranslatedLayoutMixin from "~/components/TranslatedLayoutMixin.js";

// Compared to the native component :
// - Options menu is hidden in translations
// TODO : Gui has been disabled, but copy/paste and some other actions might still work (batch, etc...)

export default {
	extends: 'k-block',
	mixins: [
        TranslatedLayoutMixin,
    ],
};
</script>

<style>

</style>
