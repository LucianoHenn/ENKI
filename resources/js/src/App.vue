<template>
    <router-view></router-view>
</template>
<script setup>
    import '@/assets/sass/app.scss';
    import { useMeta } from '@/composables/use-meta';
    useMeta({ title: 'Sales Admin' });
</script>
