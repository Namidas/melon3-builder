/* eslint-disable */

import './styles.scss'
import { defineComponent, ref } from 'vue'
import _ from 'lodash'

const proxiable = [
    'inlineActions',
    'dense',
    'rounded',
    'dark',
]

const proxiableProps = {}
for (const x in proxiable)
    proxiableProps[proxiable[x]] = {
        required: false
    }

const $_COMPONENT = defineComponent({
    name: 'cimpl-banner',
    tagname: 'cimpl-banner',

    props: {
        ...proxiableProps,

        /*will automatically add the class bg-[backgroundColor]*/
        backgroundColor: {
            required: false
        },

        /*icon/avatar and text color in quasar format*/
        color: {
            required: false,
            type: String
        },

        /*icon/avatar size in quasar format*/
        size: {
            required: false,
            type: String,
            default: "40px"
        },

        /*progress indicator color in quasar format, "false" to default to [color]*/
        textColor: {
            required: false,
            type: String
        },

        /*progress indicator color in quasar format, "false" to default to [color]*/
        loadingColor: {
            required: false,
            type: String
        },

        /*progress indicator size in quasar format, "false" to default to [size]*/
        loadingSize: {
            required: false,
            type: String
        },

        /*icon name to use on q-icon on avatar slot*/
        icon: {
            required: false,
            type: [Boolean, String],
            default: false
        },

        /*progress indicator to use when [loading] is "true"
        false: don't use a progress indicator [keep the icon]
        string: component name to use with <component />
        object: component definition as-is
        */
        progressIndicator: {
            required: false,
            type: [Boolean, String, Object],
            default: 'q-circular-progress'
        },

        /*loading state for the component, automatically changes current icon for the progress indicator*/
        loading: {
            required: false,
            type: Boolean,
            default: false
        },

        /*props proxy for the q-icon*/
        iconProxy: {
            required: false,
            type: Object,
            default: {}
        },

        /*props proxy for the progress indicator component*/
        progressIndicatorProxy: {
            required: false,
            type: Object,
            default: {
                indeterminate: true,
                rounded: true
            }
        },

        //border related props
        /*bordered: {
            required: false,
            type: Boolean,
            default: false,
        },

        borderColor: {
            required: false,
            type: String,
        }*/
    },

    emits: [],

    watch: {
    },

    setup (props) {

        let setupData = {
        }

        return setupData
    },

    computed: {
        /*props proxeable props for the banner*/
        bannerProxy () {
            const proxy = {}
            for (const x in proxiable)
                proxy[proxiable[x]] = this[proxiable[x]]
            return proxy
        },

        /*banner CSS classess*/
        bannerClassess () {
            var result = [
                this.backgroundColor !== undefined ? `bg-${this.backgroundColor}` : undefined,
                this.textColor !== undefined ? `text-${this.textColor}` : (this.color !== undefined ? `text-${this.color}` : ''),
            ]
            return result
        },

        /*wether or not to render the avatar slot and template
        it renders if:
        - icon is not false
        - progressIndicator is not false and the loading state is set to true
        - there's an avatar slot defined on instance
        */
        renderAvatar () {
            return this.icon !== false || (this.loading && this.progressIndicator !== false) || this.$slots.avatar !== undefined
        },

        /*wether or not to render the avatar icon
        it renders if:
        - icon is not false AND (loading state is false OR progress indicator is false)
        */
        renderIcon () {
            return this.icon !== false && (!this.loading || this.progressIndicator === false)
        },

        renderProgress () {
            return this.progressIndicator !== false && this.loading
        },

        computed_iconProxy () {
            const proxy = {
                name: this.icon
            }

            if (this.size !== undefined)
                proxy.size = this.size

            if (this.color !== undefined)
                proxy.color = this.color

            for (const x in this.iconProxy)
                proxy[x] = this.iconProxy[x]

            return proxy
        },

        computed_progressProxy () {
            const proxy = {}

            if (this.loadingSize !== undefined)
                proxy.size = this.loadingSize
            else if (this.size !== undefined)
                proxy.size = this.size

            if (this.loadingColor !== undefined)
                proxy.color = this.loadingColor
            else if (this.color !== undefined)
                proxy.color = this.color

            for (const x in this.progressIndicatorProxy)
                proxy[x] = this.progressIndicatorProxy[x]

            return proxy
        }
    },

    methods: {

    },
})

export default $_COMPONENT