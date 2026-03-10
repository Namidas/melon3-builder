<template>
  <q-layout view="lHh Lpr lFf">
    <q-header
      class="bg-white q-px-md"
      :class="{building: projectStore.building}"
      elevated
      reveal
      :model-value="projectStore.hasProject"
      >
      <ProjectMain />
    </q-header>

    <q-drawer
      :model-value="projectStore.hasProject"
      bordered
    >
      <q-list>
        <q-item-label
          header
        >
          Project menu
        </q-item-label>

        <EssentialLink
          v-for="link in linksList"
          :key="link.title"
          v-bind="link"
        />
      </q-list>
    </q-drawer>

    <q-page-container :class="{building: projectStore.building}">
      <router-view v-if="projectStore.hasProject" />
      <q-page class="flex flex-center home-page" v-else>
        <q-card class="q-px-md" style="width: 100%; max-width: 500px;padding-bottom: 0;">
          <ProjectMain />
        </q-card>
      </q-page>

      <q-footer class="bg-white" bordered :model-value="projectStore.hasProject">
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
      </q-footer>
    </q-page-container>
  </q-layout>
</template>

<style lang="scss">
  .full-width-fix
  {
    position:  relative;
    width: calc(100% + 32px);
    left: -16px;
  }

  .home-page
  {
    background: url('/public/img/homebg.gif');
  }

  .q-page-container,
  .q-header
  {
    &.building
    {
      &::after
      {
        content: "";
        display: inline-block;
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        background: rgba(white,.5);
      }
    }
  }
</style>

<script>
/* eslint-disable */
import { ref, computed, defineComponent } from 'vue'
import EssentialLink from 'components/EssentialLink.vue'
import { useProjectStore } from 'stores/project'
import ProjectMain from 'components/ProjectMain.vue'

export default defineComponent({
  name: 'MainLayout',

  components: {
    EssentialLink,
    ProjectMain
  },

  setup (props) {
    const projectStore = useProjectStore()
    return {
      projectStore,
      leftDrawerOpen: ref(false),
      linksList: [
        {
          title: 'Details',
          //caption: 'quasar.dev',
          icon: 'school',
          link: '/details'
        },
        {
          title: 'GIT',
          //caption: 'github.com/quasarframework',
          icon: 'code',
          link: '/git'
        },
        {
          title: 'Build',
          //caption: 'chat.quasar.dev',
          icon: 'build',
          link: '/build'
        },
      ]
    }
  },

  computed: {
  },

  methods: {
    toggleLeftDrawer () {
      this.leftDrawerOpen.value = !this.leftDrawerOpen.value
    }
  }
})

</script>
