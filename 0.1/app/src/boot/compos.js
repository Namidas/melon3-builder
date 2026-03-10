/* eslint-disable */
import { boot } from 'quasar/wrappers'

import CImplBanner from 'components/cimpl-banner.vue'

export default boot(async ({ app }) => {
    app.component('cimpl-banner', CImplBanner)
})
