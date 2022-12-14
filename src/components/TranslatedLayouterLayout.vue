<template>
	<section
		:data-selected="isSelected"
		class="k-layout"
		tabindex="0"
		@click="$emit('select')"
	>
		<k-grid class="k-layout-columns">
			<k-layout-column
				v-for="(column, columnIndex) in columns"
				:key="column.id"
				:endpoints="endpoints"
				:fieldset-groups="fieldsetGroups"
				:fieldsets="fieldsets"
				v-bind="column"
				@input="
					$emit('updateColumn', {
						column,
						columnIndex,
						blocks: $event
					})
				"
			/>
		</k-grid>
		<nav v-if="!disabled" class="k-layout-toolbar">
			<k-button
				v-if="settings"
				:tooltip="$t('settings')"
				class="k-layout-toolbar-button"
				icon="settings"
				@click="$refs.drawer.open()"
			/>

			<k-dropdown v-if="!layoutEditingIsDisabled"><!-- new v-if compared to native -->
				<k-button
					class="k-layout-toolbar-button"
					icon="angle-down"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content ref="options" align="right">
					<k-dropdown-item icon="angle-up" @click="$emit('prepend')">
						{{ $t("insert.before") }}
					</k-dropdown-item>
					<k-dropdown-item icon="angle-down" @click="$emit('append')">
						{{ $t("insert.after") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						v-if="settings"
						icon="settings"
						@click="$refs.drawer.open()"
					>
						{{ $t("settings") }}
					</k-dropdown-item>
					<k-dropdown-item icon="copy" @click="$emit('duplicate')">
						{{ $t("duplicate") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						icon="trash"
						@click="$refs.confirmRemoveDialog.open()"
					>
						{{ $t("field.layout.delete") }}
					</k-dropdown-item>
				</k-dropdown-content>
			</k-dropdown>
			<k-sort-handle v-if="!layoutEditingIsDisabled" /><!-- new v-if compared to native -->
		</nav>

		<k-form-drawer
			v-if="settings"
			ref="drawer"
			:tabs="tabs"
			:title="$t('settings')"
			:value="attrs"
			class="k-layout-drawer"
			icon="settings"
			@input="$emit('updateAttrs', $event)"
		/>

		<k-remove-dialog
			ref="confirmRemoveDialog"
			:text="$t('field.layout.delete.confirm')"
			@submit="$emit('remove')"
		/>
	</section>
</template>

<script>
import Column from "@KirbyPanel/components/Layouter/Column.vue";
import KLayout from "@KirbyPanel/components/Layouter/Layout.vue";
import TranslatedLayoutMixin from "~/components/TranslatedLayoutMixin.js";

/**
 * @internal
 */
export default {
	extends: KLayout,//"k-layout",
	components: {
		"k-layout-column": Column
	},
	mixins: [
		TranslatedLayoutMixin
	],
};
</script>

<style>

</style>
