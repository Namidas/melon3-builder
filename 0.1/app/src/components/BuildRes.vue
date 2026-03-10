<template>
    <div class="project-build-res">
        <div class="builder-indicator flex flex-center column full-width q-ma-lg" v-if="showNoBuilt">
            <q-icon name="code_off" size="xl" color="grey-7" />
            <br />
            {{$t('project.no_build')}}
        </div>
        <div class="full-width" v-if="res">
            <div class="full-width" v-if="!res.error">
                <q-markup-table>
                    <tbody>
                        <ProjectDataRow v-for="(data,key) in res.data" :key="key"
                        :data-key="key"
                        :data-value="data"
                        />
                    </tbody>
                </q-markup-table>
            </div>
            <div class="full-width" v-if="res.error && !building">
                <cimpl-banner icon="error" background-color="red" text-color="white" class="full-width">
                    <b>#{{res.error_no}}:</b> {{$t(res.error_msg)}}
                </cimpl-banner>
                <q-markup-table>
                    <tbody>
                        <ProjectDataRow v-for="(data,key) in res" :key="key"
                        :data-key="key"
                        :data-value="data"
                        />
                    </tbody>
                </q-markup-table>
            </div>
        </div>
    </div>
</template>

<script>
/*eslint-disable*/
import { defineComponent } from 'vue'
import _ from 'lodash'
import ProjectDataRow from 'components/ProjectDataRow.vue'
//import { useProjectStore } from 'stores/project'

export default defineComponent({
    name: 'BuildRes',

    components: {
        ProjectDataRow
    },

    props: {
        res: {
            required: true,
            default: false,
            type: [Boolean, Object]
        },

        building: {
            required: false,
            type: Boolean,
            default: false
        }
    },

    setup() {
        return {
            //projectStore: useProjectStore(),
        }
    },

    computed: {
        showNoBuilt()
        {
            return !this.building && (!this.res || _.get(this.res,'data.error',true))
        }
    }
})
</script>