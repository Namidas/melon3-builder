<template>
  <q-page>
    <div class="row full-width q-pa-md">
      <cimpl-banner v-if="!projectStore.hasProject && !projectStore.loadingPath" background-color="grey-3" class="full-width text-grey-7">
        {{$t('project.no_loaded')}}
      </cimpl-banner>
      <div class="col-12" v-else>
          <div class="builder-indicator flex flex-center full-width q-my-xl" v-if="projectStore.building">
            <BuildingAnimation style="width: 100px; height: 100px;" />
          </div>
          <BuildRes :res="projectStore.buildRes" :building="projectStore.building" />
          <!--<q-footer class="bg-white" bordered :model-value="projectStore.hasProject">
            <q-toolbar>
              <cimpl-banner
                :loading="true"
                text-color="grey-8"
                loading-size="sm"
                v-if="projectStore.building"
                >
                  {{$t('project.building')}}
              </cimpl-banner>
              <q-space />
              <q-btn color="primary" :disable="projectStore.building" :label="$t('project.submit')" @click="projectStore.buildProject" />
            </q-toolbar>
          </q-footer>-->
        </div>
    </div>
  </q-page>
</template>

<style lang="scss">

</style>

<script>
import { defineComponent } from 'vue'
import { useProjectStore } from 'stores/project'
import BuildingAnimation from 'components/BuildingAnimation.vue'
import BuildRes from 'components/BuildRes.vue'

export default defineComponent({
  name: 'BuildPage',

  components: {
    BuildingAnimation,
    BuildRes
  },

  setup () {
    const projectStore = useProjectStore()
    return {
      projectStore
    }
  },

  computed: {
  },

  methods: {
  }
})
</script>
