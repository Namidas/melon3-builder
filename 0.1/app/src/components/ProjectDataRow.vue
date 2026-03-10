<template>
    <tr class="data-row-tr" :class="{last: lastRow}">
        <th class="data-row-th">
            <div class="sticky-it">
                {{headLabel}}
                <span v-if="isNested">
                    <q-btn
                        icon="unfold_more"
                        flat
                        :size="'xs'"
                        class="nester q-pa-xs"
                        :color="nestedOpen ? 'primary' : undefined"
                        @click="nestedOpen = !nestedOpen"
                        />
                </span>
                <q-tooltip v-if="showKeyTooltip">{{dataKey}}<span v-if="dataType"> / {{dataType}}</span></q-tooltip>
            </div>
        </th>
        <td :style="tdStyles">
            <q-markup-table v-if="isNested && nestedOpen" flat>
                <tbody>
                    <ProjectDataRow v-for="(data,key) in valueDataRows" :key="key"
                        :data-key="key"
                        :data-type="data.type"
                        :data-value="data.value"
                        :last-row="data.last"
                        :nested-start-open="nestedOpen"
                        />
                </tbody>
            </q-markup-table>
            <span class="text-value" :class="{isNested}" v-else>{{dataValue}}</span>
        </td>
    </tr>
</template>

<style lang="scss">
    .data-row-tr
    {
        & > .data-row-th
        {
            position: relative;
            border-right: 1px dashed #dadada;
            border-bottom: 1px solid #dadada;
            text-align: right;

            & > .sticky-it
            {
                //position: sticky;
                //top: 40px;
            }
        }

        .nester
        {
            margin-left: 5px;
        }

        .text-value.isNested
        {
            //white-space: normal;
            //width: 100%;ç
            width: 100%;
            max-width: calc(100vw - 600px);
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }

        &.last
        {
            & > .data-row-th
            {
                border-bottom: 0;
            }
        }
    }
</style>

<script>
/* eslint-disable */
import { defineComponent, ref } from 'vue'

export default defineComponent({
    name: 'ProjectDataRow',

    props: {
        dataKey: {
            required: true,
        },

        dataType: {
            required: false,
            type: [Boolean,String],
            default: false
        },

        dataValue: {
            required: true,
        },

        label: {
            required: false,
            type: [String,Boolean],
            default: false,
        },

        lastRow: {
            type: Boolean,
            required: false,
            default: false
        },

        nestedStartOpen: {
            type: Boolean,
            required: false,
            default: false,
        }
    },

    watch: {
        nestedStartOpen(newValue) {
            this.nestedOpen = newValue ? true : false
        }
    },

    computed: {
        showKeyTooltip() {
            return this.label !== false || this.dataType !== false
        },
        
        headLabel() {
            return this.label === false ? this.dataKey : this.label
        },

        isNested() {
            const nestedTypes = [
                'array',
                'object'
            ]
            return nestedTypes.includes(this.dataType)
        },

        valueDataRows () {
            const rows = {}
            const filter = [
                //'name',
                //'version',
                //'parsed_files'
            ]
            const keys = Object.keys(this.dataValue)
            const len = keys.length
            let curr = 0
            for (const x in keys) {
                curr++
                if (!filter.includes(keys[x])) {
                    const value = this.dataValue[keys[x]]
                    let type = typeof value
                    if (Array.isArray(value)) type = 'array'
                    rows[keys[x]] = {
                        type,
                        value,
                        last:  curr === len
                    }
                }
            }
            return rows
        },

        tdStyles () {
            const styles = {
                width: '100%',
            }
            if(this.isNested && this.nestedOpen) styles.padding = 0
            return styles
        }
    },

    setup (props) {
        return {
            nestedOpen: ref(props.nestedStartOpen ? true : false)
        }
    }
})
</script>
