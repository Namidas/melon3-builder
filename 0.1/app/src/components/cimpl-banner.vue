<template>
    <q-banner
        class="cimpl-banner"
        v-bind="bannerProxy"
        :class="bannerClassess"
        >
        <template #avatar v-if="renderAvatar">
            <slot name="avatar">
                <!--here goes the magic....-->
                <q-icon
                    v-if="renderIcon"
                    v-bind="computed_iconProxy"
                    />
                <component
                    :is="progressIndicator"
                    v-if="renderProgress"
                    v-bind="computed_progressProxy"
                    />
            </slot>
        </template>
        <template #default v-if="$slots.default">
            <slot name="default" />
        </template>
        <template #action v-if="$slots.action">
            <slot name="action" />
        </template>
    </q-banner>

    <!--bannerClassess ? {{bannerClassess}}<br />
    icon ? {{icon}}<br />
    renderIcon ? {{renderIcon}}<br />
    renderProgress ? {{renderProgress}}<br />
    loading ? {{loading}}<br />
    computed_progressProxy ? {{computed_progressProxy}}<br />
    $slots.avatar ? {{$slots.avatar !== undefined}}<br />
    $slots.default ? {{$slots.default !== undefined}}<br />
    $slots.action ? {{$slots.action !== undefined}}<br />-->
</template>

<script src="./banner/component.js"></script>
