<template>
  <router-view />

  <!-- Toast stays mounted globally -->
  <AppToast ref="toast" />
</template>

<script setup>
import { ref, provide } from "vue";
import AppToast from "./common/AppToast.vue";

const toast = ref(null);

// Make toast available everywhere
provide("toast", toast);
</script>
