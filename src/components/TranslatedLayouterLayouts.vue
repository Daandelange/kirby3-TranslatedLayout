<template>
	<div>
		<template v-if="rows.length">
			<k-draggable v-bind="draggableOptions" class="k-layouts" @sort="save">
				<k-translated-layout
					v-for="(layout, layoutIndex) in rows"
					:key="layout.id"
					:disabled="disabled"
					:endpoints="endpoints"
					:fieldset-groups="fieldsetGroups"
					:fieldsets="fieldsets"
					:is-selected="selected === layout.id"
					:settings="settings"
					v-bind="layout"
					@append="selectLayout(layoutIndex + 1)"
					@duplicate="duplicateLayout(layoutIndex, layout)"
					@prepend="selectLayout(layoutIndex)"
					@remove="removeLayout(layout)"
					@select="selected = layout.id"
					@updateAttrs="updateAttrs(layoutIndex, $event)"
					@updateColumn="
						updateColumn({
							layout,
							layoutIndex,
							...$event
						})
					"
				/>
			</k-draggable>

			<!-- new v-if compared to native -->
			<k-button
				v-if="!layoutEditingIsDisabled"
				class="k-layout-add-button"
				icon="add"
				@click="selectLayout(rows.length)"
			/>
		</template>
		<template v-else>
			<k-empty icon="dashboard" class="k-layout-empty" @click="selectLayout(0)">
				{{ empty || $t("field.layout.empty") }}
			</k-empty>
		</template>

		<k-dialog
			ref="selector"
			:cancel-button="false"
			:submit-button="false"
			size="medium"
			class="k-layout-selector"
		>
			<k-headline>{{ $t("field.layout.select") }}</k-headline>
			<ul>
				<li
					v-for="(layoutOption, layoutOptionIndex) in layouts"
					:key="layoutOptionIndex"
					class="k-layout-selector-option"
				>
					<k-grid @click.native="addLayout(layoutOption)">
						<k-column
							v-for="(column, columnIndex) in layoutOption"
							:key="columnIndex"
							:width="column"
						/>
					</k-grid>
				</li>
			</ul>
		</k-dialog>
	</div>
</template>

<script>
// This is a minimal copy of components/Layouter/Layouts.vue which is loaded as k-block-layouts

// The purpose of the clone is to disable layouts visually in translations while maintaining blocks editable
// (if layoutsfield.props.disabled=true, blocks are also disabled)
// Changes from the original template code are commented, to be updated when kirby updates the template code.

import TranslatedBlockLayout from "~/components/TranslatedLayouterLayout.vue";
import KBlockLayouts from "@KirbyPanel/components/Layouter/Layouts.vue";
import TranslatedLayoutMixin from "~/components/TranslatedLayoutMixin.js";

/**
 * @internal
 */
export default {
	extends: KBlockLayouts,//"k-block-layouts", // Note: component is not registered globally, we have to import it separately
	components: {
		"k-translated-layout" : TranslatedBlockLayout
	},
	mixins: [
		TranslatedLayoutMixin
	],
	mounted: function(){
		// Manually inject functions from parent ! (we need to override them and still being able to call them)
		this.addLayoutNative 		= KBlockLayouts.methods.addLayout;
		this.removeLayoutNative 	= KBlockLayouts.methods.removeLayout;
		this.duplicateLayoutNative 	= KBlockLayouts.methods.duplicateLayout;
		this.selectLayoutNative 	= KBlockLayouts.methods.selectLayout;
	},
	// Cancel some native methods that we don't need ?
	methods: {
		async addLayout(columns) {
			return this.layoutEditingIsDisabled ? null : this.addLayoutNative(columns);
		},
		duplicateLayout(index, layout) {
			return this.layoutEditingIsDisabled ? null : this.duplicateLayoutNative(index, layout);
		},
		removeLayout(layout) {
			return this.layoutEditingIsDisabled ? null : this.removeLayoutNative(layout);
		},
		selectLayout(index) {
			return this.layoutEditingIsDisabled ? null : this.selectLayoutNative(index);
		},
	},

};
</script>

<style>

</style>
