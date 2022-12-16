<template>
	<k-field v-bind="$props" class="k-blocks-field">
		<template #options>
			<k-dropdown v-if="hasFieldsets && !layoutEditingIsDisabled"><!-- different v-if compared to native template -->
				<k-button icon="dots" @click="$refs.options.toggle()" />
				<k-dropdown-content ref="options" align="right">
					<k-dropdown-item
						:disabled="isFull"
						icon="add"
						@click="$refs.blocks.choose(value.length)"
					>
						{{ $t("add") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						:disabled="isEmpty"
						icon="template"
						@click="$refs.blocks.copyAll()"
					>
						{{ $t("copy.all") }}
					</k-dropdown-item>
					<k-dropdown-item
						:disabled="isFull"
						icon="download"
						@click="$refs.blocks.pasteboard()"
					>
						{{ $t("paste") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						:disabled="isEmpty"
						icon="trash"
						@click="$refs.blocks.confirmToRemoveAll()"
					>
						{{ $t("delete.all") }}
					</k-dropdown-item>
				</k-dropdown-content>
			</k-dropdown>
		</template>

    <!-- new block compared to native template NOPE -->
    <k-empty v-if="layoutEditingIsDisabled && empty"
      class="k-blocks-empty"
      icon="box"
    >
      {{ empty || $t("field.blocks.empty") }}
    </k-empty>
		<k-blocks
      v-else
			ref="blocks"
			:autofocus="autofocus"
			:compact="false"
			:empty="empty"
			:endpoints="endpoints"
			:fieldsets="fieldsets"
			:fieldset-groups="fieldsetGroups"
			:group="group"
			:max="max"
			:value="value"
			@close="opened = $event"
			@open="opened = $event"
			v-on="$listeners"
		/><!-- new v-else compared to native template NOPE -->

		<k-button
			v-if="!isEmpty && !isFull && !layoutEditingIsDisabled"
			class="k-field-add-item-button"
			icon="add"
			:tooltip="$t('add')"
			@click="$refs.blocks.choose(value.length)"
		/><!-- different v-if compared to native template -->
	</k-field>
</template>

<script>
import TranslatedLayoutMixin from "~/components/TranslatedLayoutMixin.js";

export default {
    extends: 'k-blocks-field',
    components: {
        //'k-translated-block-layouts' : TranslatedBlockLayouts,
    },
    mixins: [
        TranslatedLayoutMixin,
    ],
};
</script>

<style>

</style>
