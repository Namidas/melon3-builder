const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '/details', name: 'details', component: () => import('pages/IndexPage.vue') },
      { path: '/git', name: 'git', component: () => import('pages/GitPage.vue') },
      { path: '/build', name: 'build', component: () => import('pages/BuildPage.vue') }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
