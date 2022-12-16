<template>
	<div
		ref="wrapper"
		:data-empty="blocks.length === 0"
		:data-multi-select-key="isMultiSelectKey"
		class="k-blocks"
		@focusin="focussed = true"
		@focusout="focussed = false"
	>
		<template v-if="hasFieldsets && !layoutEditingIsDisabled">
			<k-draggable v-bind="draggableOptions" class="k-blocks-list" @sort="save">
				<k-block
					v-for="(block, index) in blocks"
					:ref="'block-' + block.id"
					:key="block.id"
					v-bind="block"
					:endpoints="endpoints"
					:fieldset="fieldset(block)"
					:is-batched="isBatched(block)"
					:is-last-in-batch="isLastInBatch(block)"
					:is-full="isFull"
					:is-hidden="block.isHidden === true"
					:is-selected="isSelected(block)"
					:next="prevNext(index + 1)"
					:prev="prevNext(index - 1)"
					@append="append($event, index + 1)"
					@blur="select(null)"
					@choose="choose($event)"
					@chooseToAppend="choose(index + 1)"
					@chooseToConvert="chooseToConvert(block)"
					@chooseToPrepend="choose(index)"
					@copy="copy()"
					@confirmToRemoveSelected="confirmToRemoveSelected"
					@click.native.stop="select(block, $event)"
					@duplicate="duplicate(block, index)"
					@focus="select(block)"
					@hide="hide(block)"
					@paste="pasteboard()"
					@prepend="add($event, index)"
					@remove="remove(block)"
					@sortDown="sort(block, index, index + 1)"
					@sortUp="sort(block, index, index - 1)"
					@show="show(block)"
					@update="update(block, $event)"
				/>
				<template #footer>
					<k-empty
						v-if="!layoutEditingIsDisabled"
						class="k-blocks-empty"
						icon="box"
						@click="choose(blocks.length)"
					><!-- new v-if compared to native template -->
						{{ empty || $t("field.blocks.empty") }}
					</k-empty>
				</template>
			</k-draggable>

			<k-block-selector
				ref="selector"
				:fieldsets="fieldsets"
				:fieldset-groups="fieldsetGroups"
				@add="add"
				@convert="convert"
				@paste="paste($event)"
			/>

			<k-remove-dialog
				ref="removeAll"
				:text="$t('field.blocks.delete.confirm.all')"
				@submit="removeAll"
			/>

			<k-remove-dialog
				ref="removeSelected"
				:text="$t('field.blocks.delete.confirm.selected')"
				@submit="removeSelected"
			/>

			<k-block-pasteboard ref="pasteboard" @paste="paste($event)" />
		</template>
		<template v-else>
			<k-box theme="info"> No fieldsets yet </k-box>
		</template>
	</div>
</template>

<script>

// Override needed to cancel the add dialog when blocks is empty
// Todo: change mouse pointer on hover ?
// Note: now moved to TranslatedBlocksField

import TranslatedLayoutMixin from "~/components/TranslatedLayoutMixin.js";

export default {
	extends: 'k-blocks',
	// components: {
	// 	'k-translated-block-layouts' : TranslatedBlockLayouts,
	// },
	mixins: [
        TranslatedLayoutMixin,
    ],
	methods: {
		choose(index){
			if(this.layoutEditingIsDisabled) return;
			this.originalChosse(index);
		},
	}
}
</script>

<style>

</style>
