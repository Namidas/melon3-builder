<template>
    <div class="full-width">
        <!--<q-btn label="CLOSE PROJECT" @click="projectStore.closeCurrentProject" />-->
        <q-input v-if="!projectStore.loadingPath"
          :label="$t('project_src_path.title')"
          :placeholder="$t('project_src_path.placeholder')"
          v-model="projectStore.projectSrcPath"
          :loading="projectStore.checkingPath"
          :borderless="!projectStore.hasProject && !projectStore.hasKnownProjects"
          @update:model-value="changedProjectPathFromInput"
          debounce="500"
          :readonly="projectStore.loadingPath || projectStore.hasProject"
          >
          <template #append v-if="projectStore.pseudoValidSrcPath">
            <div class="row">
                <q-btn icon="refresh" flat @click="projectStore.checkSrcPath">
                    <q-tooltip>{{$t('project_src_path.reload')}}</q-tooltip>
                </q-btn>
                <q-separator vertical spaced v-if="projectStore.loadableSrcPath && !projectStore.hasProject" />
                <q-btn icon="account_tree" flat @click="onClickLoadProject" v-if="projectStore.loadableSrcPath && !projectStore.hasProject">
                    <q-tooltip>{{$t('project.load')}}</q-tooltip>
                </q-btn>
                <q-separator vertical spaced v-if="projectStore.hasProject" />
                <q-btn icon="close" flat @click="projectStore.closeCurrentProject()" v-if="projectStore.hasProject">
                    <q-tooltip>{{$t('project.close_current')}}</q-tooltip>
                </q-btn>
            </div>
          </template>
          <q-linear-progress v-if="debouncingProjectInputChange" indeterminate style="position: absolute; left: 0; bottom: 0; width: 100%; height: 2px" />
        </q-input>
        <cimpl-banner class="loadable-path-data bg-blue text-white" :class="{'full-width': !projectStore.loadingPath, 'full-width-fix': projectStore.loadingPath}" v-if="projectStore.checkedPathResult !== false && !projectStore.hasProject">
          <div class="title">{{projectStore.checkedPathResult.title}}</div>
          <div class="path">{{projectStore.checkedPathResult.name}} v{{projectStore.checkedPathResult.version}}</div>
        </cimpl-banner>
        <q-select v-if="!projectStore.loadingPath && !projectStore.hasProject"
          v-model="projectStore.knownProject"
          :options="projectStore.knownProjects"
          @update:model-value="changedKnownProject"
          borderless
          :readonly="projectStore.loadingPath"
          >
          <template v-slot:option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section>
                <q-item-label>{{ scope.opt.label }}</q-item-label>
                <q-item-label caption>{{ scope.opt.value }}</q-item-label>
              </q-item-section>
              <q-item-section avatar>
                <q-btn icon="remove_circle_outline"
                  rounded
                  size="xs"
                  flat
                  @click.stop="projectStore.removeKnownProjectIndex(scope.index)"
                  >
                  <q-tooltip>{{$t('project.known.remove')}}</q-tooltip>
                </q-btn>
              </q-item-section>
            </q-item>
          </template>
        </q-select>
        <div class="full-width row" v-if="projectStore.hasProject">
          <q-input class="col-8" readonly :label="$t('project.title')" v-model="projectStore.currentProject.title" borderless />
          <q-input class="col-3" readonly :label="$t('project.name')" v-model="projectStore.currentProject.name" borderless />
          <q-input class="col-1" readonly :label="$t('project.version')" v-model="projectStore.currentProject.version" borderless />
        </div>
        <cimpl-banner
          v-if="projectStore.loadingPath"
          :loading="true"
          background-color="grey-13"
          class="full-width-fix"
          loading-size="sm"
          >
            {{$t('project.loading')}}
        </cimpl-banner>
        <cimpl-banner v-if="hasErrorLoading" icon="error" background-color="red" text-color="white" class="full-width-fix">
          <b>#{{projectStore.loadError.response.data.error_no}}:</b> {{$t(projectStore.loadError.response.data.error_msg)}}
        </cimpl-banner>
    </div>
</template>

<style lang="scss">
</style>

<script>
/* eslint-disable */
import { defineComponent, ref } from 'vue'
import { useProjectStore } from 'stores/project'
import _ from 'lodash'

export default defineComponent({
    name: 'ProjectMain',

    setup (props) {
        return {
            projectStore: useProjectStore(),
            nestedOpen: ref(props.nestedStartOpen ? true : false),
            debouncingProjectInputChange: ref(false),
            projectInputChangeDebouncer: null
        }
    },

    methods: {
      changedKnownProject(payload)
      {
        //console.log("changed known",payload)

        if(payload.value.trim() !== '')
        {
          this.projectStore.checkedPathResult = payload
          this.projectStore.loadingPath = true
          const ctx = this
          this.projectStore.projectSrcPath = payload.value
          
          this.projectStore.checkSrcPath(false).then((response) => {
            this.projectStore.loadingPath = false
            console.log("FINISHED CHECKING",response.data.data)
            /*if(res) */ctx.projectStore.loadSrcPath(response.data.data)
            //else ctx.projectStore.checkedPathResult = false
          })
        }
        else
        {
          this.projectStore.checkedPathResult = false
          this.projectStore.loadError = false
        }
      },

      changedProjectPathFromInput(payload)
      {
        const ctx = this
        this.projectStore.setCurrentKnownProject(payload)
        this.debouncingProjectInputChange = true
        if(this.projectInputChangeDebouncer !== null)
          clearTimeout(this.projectInputChangeDebouncer)
        ctx.projectInputChangeDebouncer = setTimeout(() => {
          ctx.projectStore.checkSrcPath()
          ctx.debouncingProjectInputChange = false
        },500)
      },

      onClickLoadProject()
      {
        this.projectStore.loadSrcPath(this.projectStore.checkedPathResult !== false ? this.projectStore.checkedPathResult : undefined)
      }
    },

    computed: {
      hasErrorLoading() {
        return _.get(this.projectStore.loadError,'response.data') ? true : false
      },

      showLoadingBanner() {
        return this.projectStore.checkedPathResult !== false && !this.projectStore.hasProject
      }
    }
})
</script>
<style lang="scss">
  .loadable-path-data
  {
    .title
    {
      font-weight: bold;
    }

    .path
    {
      font-size: 10px;
    }

    .q-banner__content
    {
      display: flex;
      justify-content: space-between;
    }
  }
</style>