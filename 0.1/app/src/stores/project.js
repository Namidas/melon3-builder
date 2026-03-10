/* eslint-disable */
import { defineStore } from 'pinia'
import axios from 'axios'

const knownProject_empty = { label: '...', value: ''}

export const useProjectStore = defineStore('project', {
  state: () => ({
    lowercaseCompareProjectPaths: true,
    serverURL: 'http://localhost/Namida/melon3_builder/0.1/server/',
    projectSrcPath: '',
    lastCheckedProjectSrcPath: '',
    knownProject: {...knownProject_empty},
    knownProjects: [
      {...knownProject_empty}
    ],
    loadableSrcPath: false,
    loadingPath: false,
    checkingPath: false,
    checkedPathResult: false,
    currentProject: false,
    loadError: false,
    building: false,
    buildRes: false,
  }),
  getters: {
    hasProject: (state) => state.currentProject !== false/*Object.keys(state.currentProject).length > 0*/,
    hasKnownProjects: (state) => state.knownProjects.length > 1,
    pseudoValidSrcPath: (state)  => state.projectSrcPath.trim() !== '' && !state.loadingPath,
  },
  actions: {
    setCurrentKnownProject(path)
    {
      for(const x in this.knownProjects)
      {
        const proj = this.knownProjects[x]
        if((this.lowercaseCompareProjectPaths ? proj.value.toLowerCase() : proj.value) === (this.lowercaseCompareProjectPaths ? path.toLowerCase() : path))
        {
          this.knownProject = proj
          return
        }
      }
      this.knownProject = knownProject_empty
    },

    removeKnownProjectIndex(index)
    {
      if(index)
      {
        const removed = this.knownProjects.splice(index)
        if(this.knownProject === removed)
        {
          this.projectSrcPath = knownProject_empty.value
          this.setCurrentKnownProject(knownProject_empty.value)
        }
      }
    },

    appendKnownProject(project)
    {
      var found = false
      for(const x in this.knownProjects)
        if((this.lowercaseCompareProjectPaths ? this.knownProjects[x].value.toLowerCase() : this.knownProjects[x].value) === (this.lowercaseCompareProjectPaths ? project.build_source.toLowerCase() : project.build_source))
          found = true
      if(!found)
        this.knownProjects.push({
          label: `${project.title} (${project.name} v${project.version})`,
          value: project.build_source,
          title: project.title,
          'name': project.name,
          version: project.version
        })
    },

    checkSrcPath (voidCurrentRes) {
      if(voidCurrentRes === undefined) voidCurrentRes = true
      const ctx = this
      ctx.checkingPath = true
      ctx.loadError = false
      if(voidCurrentRes) ctx.checkedPathResult = false
      const axiosRequest = axios.get(ctx.serverURL, {
          params: {
            action: 'get_project',
            path: ctx.projectSrcPath,
            client: 'melon3.vue3_quasar',
            controller: 'pkg'
          }
        })
        
      axiosRequest.then(function (response) {
          ctx.loadError = false
          ctx.loadableSrcPath = true
          ctx.checkedPathResult = response.data.data
        }).catch(function (error) {
          ctx.loadError = error
          ctx.loadableSrcPath = false
        }).finally(function () {
          ctx.checkingPath = false
        })

      return axiosRequest
    },

    loadSrcPath (projectData) {
      const ctx = this
      ctx.loadingPath = true
      ctx.currentProject = false

      function loadThen()
      {
        ctx.currentProject = projectData
        ctx.appendKnownProject(projectData)
        ctx.router.push({ name: 'details' })
      }

      function loadFinally()
      {
        ctx.loadingPath = false
      }

      if(projectData === undefined)
        axios.get(ctx.serverURL, {
            params: {
              action: 'get_project',
              path: ctx.projectSrcPath,
              client: 'melon3.vue3_quasar',
              controller: 'pkg'
            }
          }).then(function (response) {
            projectData = response.data.data
            ctx.loadError = false
            loadThen()
          }).catch(function (error) {
            ctx.loadError = error
            ctx.router.push('/')
          }).finally(loadFinally)
      else
      {
        loadThen()
        loadFinally()
      }
    },

    buildProject () {
      const ctx = this
      ctx.building = true
      ctx.router.push({ name: 'build' })
      axios.get(ctx.serverURL, {
        params: {
          action: 'build_project',
          path: ctx.projectSrcPath,
          client: 'melon3.vue3_quasar',
          controller: 'pkg'
        }
      }).then(function (response) {
        ctx.buildRes = response
      }).catch(function (error) {
        ctx.buildRes = error.response.data
      }).finally(function () {
        ctx.building = false
      })
    },

    closeCurrentProject()
    {
      this.currentProject = false
    }
  },

  persist: {
    pick: [
      'projectSrcPath',
      'knownProjects',
      'currentProject'
    ]
  }
})
