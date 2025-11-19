<template>
  <div
    class="toast-container position-fixed top-0 end-0 p-3"
    style="z-index: 2000"
  >
    <div
      v-for="(t, i) in toasts"
      :key="i"
      class="toast align-items-center text-white border-0 show mb-2"
      :class="t.type === 'success' ? 'bg-success' : 'bg-danger'"
      role="alert"
    >
      <div class="d-flex">
        <div class="toast-body">
          <i class="fa" :class="t.type === 'success' ? 'fa-check-circle' : 'fa-times-circle'"></i>
          {{ t.message }}
        </div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          @click="removeToast(i)"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const toasts = ref([]);

function showToast(type, message) {
  toasts.value.push({ type, message });
  setTimeout(() => removeToast(0), 3000);
}

function removeToast(index) {
  toasts.value.splice(index, 1);
}

defineExpose({ showToast });
</script>
