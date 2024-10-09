<script  setup>

import { useDoctorStore } from '../../../stores/store-doctors'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import { ref, watch } from 'vue';
import {vaah} from "../../../vaahvue/pinia/vaah";

const store = useDoctorStore();
const show_preferred = ref(false);

watch(() => store.show_status_panel, (newValue) => {
    if (!newValue) {
        show_preferred.value = false;
    }
})
</script>
<template>
    <Sidebar v-model:visible="store.show_status_panel"  header="Sidebar" position="right" style="width:800px;">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
    </Sidebar>
</template>


