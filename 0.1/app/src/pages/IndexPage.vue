<template>
  <q-page>
    <div class="row full-width q-pa-md">
      <cimpl-banner v-if="!projectStore.hasProject && !projectStore.loadingPath" background-color="grey-3" class="full-width text-grey-7">
        {{$t('project.no_loaded')}}
      </cimpl-banner>
      <div class="col-12" v-else>
        <!--<h6 class="separator-title">{{$t('project.details')}}</h6>-->
        <q-markup-table
          >
          <tbody>
            <ProjectDataRow v-for="(data,key) in projectDataRows" :key="key"
              :data-key="key"
              :data-type="data.type"
              :data-value="data.value"
              :label="$t(`project.row.${key}`)"
              />
          </tbody>
        </q-markup-table>
      </div>
    </div>
  </q-page>
</template>

<style lang="scss">

  .separator-title
  {
    margin: 0 auto 15px 0;
    padding: 0 40px 5px 10px;
    //border-bottom: 1px solid #dadada;
    border-bottom: 1px solid lighten($primary,30);
    //width: 100%;
    text-align: center;
    border-left: 10px solid #dadada;
  }

  .project-col
  {
    position: sticky;
    top: 0px;
    z-index: 100;

    .project-wrap
    {
      background: white;
      width: calc(100% + 32px);
      position: relative;
      left: -16px;
      padding: 0 16px;

      .q-field
      {
        //box-shadow: 0 5px 10px rgba(black,.1);
        //border-bottom: 1px solid var(--q-primary);
      }
    }
  }
</style>

<script>
import { defineComponent } from 'vue'
import ProjectDataRow from 'components/ProjectDataRow.vue'
import { useProjectStore } from 'stores/project'

export default defineComponent({
  name: 'IndexPage',

  components: {
    ProjectDataRow
  },

  setup () {
    const projectStore = useProjectStore()
    return {
      projectStore
    }
  },

  computed: {
    projectDataRows () {
      const rows = {}
      const filter = [
        'name',
        'version'
      ]
      const filtered = Object.keys(this.projectStore.currentProject)
        .filter(key => !filter.includes(key))
        .reduce((obj, key) => {
          obj[key] = this.projectStore.currentProject[key]
          return obj
        }, {})
      const len = filtered.length
      let curr = 0
      const keys = Object.keys(filtered)
      for (const x in keys) {
        curr++
        const value = filtered[keys[x]]
        let type = typeof value
        if (Array.isArray(value)) type = 'array'
        rows[keys[x]] = {
          type,
          value,
          last: curr === len
        }
      }
      return rows
    }
  },

  methods: {
    buildProject () {
      this.building = true
    }
  }
})
</script>
