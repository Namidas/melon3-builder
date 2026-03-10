<template>
  <q-page class="flex flex-center">
    <!--<div class="row full-width q-pa-md">-->
      <cimpl-banner v-if="!projectStore.hasProject && !projectStore.loadingPath" background-color="grey-3" class="full-width text-grey-7">
        {{$t('project.no_loaded')}}
      </cimpl-banner>
      <cimpl-banner background-color="grey-4" v-else style="min-width: 500px; text-align: center;">Soon...</cimpl-banner>
    <!--</div>-->
  </q-page>
</template>

<style lang="scss">

</style>

<script>
import { defineComponent } from 'vue'
import { useProjectStore } from 'stores/project'

export default defineComponent({
  name: 'GitPage',

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
