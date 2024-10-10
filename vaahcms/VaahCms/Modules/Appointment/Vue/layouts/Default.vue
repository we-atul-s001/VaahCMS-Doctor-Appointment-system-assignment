<script setup>
import {onMounted, reactive} from "vue";

import Aside from '../components/Aside.vue';

import { useRootStore } from '../stores/root'
const rootStore = useRootStore();


onMounted(async () => {
    await rootStore.getAssets();
});


</script>


<template>

  <div class="grid  gap-0">
    <div class="col  p-1"><RouterView /></div>
  </div>

</template>
