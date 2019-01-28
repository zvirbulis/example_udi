<template>
	<div class="handbook-popover-edit-form"
         @keyup.esc="close"
         :class="is_parent ? [metadata.class_name, 'parent-form'] : metadata.class_name"
    >
        <div class="header-panel">
            <div>
                <div class="tools-buttons">
                    <template v-if="is_parent">
                        <input class="button-blue action-button border-bt"
                               type="button"
                               @click.stop="closeParent()"
                               value="Зберегти"
                        />
                    </template>
                    <template v-else>
                        <input v-if="!readonly" class="button-blue action-button border-bt"
                               type="button"
                               @click.stop="save()"
                               value="Зберегти"
                        />
                        <input v-if="!readonly" class="button-white action-button border-bt"
                               type="button"
                               @click.stop="close()"
                               value="Відмінити"
                        />
                        <b-dropdown v-if="grid.actions && grid.actions.length > 0"
                                    variant="link" size="lg" no-caret right
                                    class="handbook-edit-form-actions"
                        >
                            <template slot="button-content">
                                <div class="special-button more-menu">
                                    <a><span></span><span></span><span></span></a>
                                </div>
                            </template>

                            <b-dropdown-item v-for="(action, actionIndex) in grid.actions" :href="action.link" :key="action.code" @click="clickAction(actionIndex)">
                                {{ action.name }}
                            </b-dropdown-item>

                        </b-dropdown>
                    </template>
                </div>
                <div class="close" v-if="!is_parent">
                    <div class="close-bt" @click.stop="close()"></div>
                </div>
            </div>
        </div>

        <!--
        <div class="tools-panel"></div>
        -->

        <div class="body-panel">
            <div class="readonly-panel" v-if="is_parent"></div>
            <div v-for="group in groups" :class="group.class_name" class="edit-form-panel">
                <div>
                    <div v-for="fieldCode in group.order" :class="fields[fieldCode].class_name" class="handbook-form-element">
                        <template v-if="fields[fieldCode].type_element === 'hidden'">
                            <input type="hidden" :name="fields[fieldCode].code" />
                        </template>

                        <template v-if="fields[fieldCode].type_element === 'custom_html'">
                            <div v-html="fields[fieldCode].value"></div>
                        </template>

                        <template v-if="fields[fieldCode].type_element === 'select_color'">
                            <handbook-form-select-color v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-select-color>
                        </template>

                        <template v-if="fields[fieldCode].type_element === 'input'">
                            <handbook-form-password v-if="fields[fieldCode].type_data === 'password'" v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-password>
                            <handbook-form-input v-else v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-input>
                        </template>

                        <template v-if="fields[fieldCode].type_element === 'textarea'">
                            <handbook-form-textarea v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-textarea>
                        </template>
                        <template v-if="fields[fieldCode].type_element === 'texteditor'">
                            <handbook-form-text-editor v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-text-editor>
                        </template>

                        <template v-if="fields[fieldCode].type_element === 'select'">
                            <handbook-form-select v-if="!isSelectAjaxField(fields[fieldCode])" v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-select>
                            <handbook-form-select-ajax v-else v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-select-ajax>
                        </template>

                        <template v-if="fields[fieldCode].type_element === 'checkbox'">
                            <handbook-form-checkbox v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-checkbox>
                        </template>
                        <template v-if="fields[fieldCode].type_element === 'file'">
                            <handbook-form-file-loader v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-file-loader>
                        </template>
                    </div>
                    <div class="handbook-form-element class-room-name">
                    </div>
                </div>
            </div>
            <div class="relations-panel" v-if="!is_parent">
                <div v-for="relation in grid.relations" v-if="relation._link.length > 0 && relation.disable !== true" :key="getHtmlId('relation', relation.code)">
                    <a :href="relation._link">{{ relation.name }}</a>
                </div>
            </div>
        </div>

        <div class="footer-panel"></div>
	</div>
</template>

<script type="text/babel">
    import mixins from './Mixins';
    import elInput from './elements/Input';
    import elPassword from './elements/Password';
    import elSelectColor from './elements/SelectColor';
    import elSelect from './elements/Select';
    import elSelectAjax from './elements/SelectAjax';
    import elTextarea from './elements/Textarea';
    import elCheckbox from './elements/Checkbox';
    import elTextEditor from './elements/TextEditor';
    import elFileLoader from "./elements/FileLoader";

    /**
     * Универсальный справочник, форма редактирования
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/BaseEditForm
     * @exports vue:components/handbooks/forms/BaseEditForm
     */
    export default {
        name: "handbook-popover-edit-form",
        mixins: [mixins],
        components: {
            'handbook-form-input': elInput,
            'handbook-form-password': elPassword,
            'handbook-form-select-color': elSelectColor,
            'handbook-form-select': elSelect,
            'handbook-form-select-ajax': elSelectAjax,
            'handbook-form-textarea': elTextarea,
            'handbook-form-checkbox': elCheckbox,
            'handbook-form-text-editor': elTextEditor,
            'handbook-form-file-loader': elFileLoader
        },
    }
</script>
