// This is just an example,
// so you can safely delete all default props below

export default {
  project_src_path: {
    title: 'Project path',
    placeholder: 'eg: \'Z:/foo\'',
    reload: 'Reload path'
  },

  project: {
    close_current: 'Close project',
    loading: 'Loading project...',
    load: 'Load project',
    no_loaded: 'No project loaded...',
    building: 'Building project...',
    no_build: 'Not built yet...',
    submit: 'Build project',
    title: 'Title',
    name: 'ID',
    version: 'Ver.',

    known: {
      remove: 'Forget project'
    },

    row: {
      title: 'Project title',
      build_source: 'Build source',
      build_path: 'Build path',
      smarty: 'Smarty configs',
      compile: 'Compile',
      compile_exclude: 'Compile exclude',
      copy: 'Copy',
      copy_exclude: 'Copy exclude',
      compilers: 'Compilers',
      docs: 'Docs',
      hooks: 'Hooks',
      parsed_files: 'Parsed files'
    }
  },

  error: {
    project_path_no_readable: 'Can\'t read project path',
    path_no_build: 'Can\'t read project build manifest',
    unknown_action: 'Unknown action',
    no_action: 'No action'
  }
}
