<template>
  <div class="status-pill" :class="status">
    <span class="status-pill__text">{{ status === 'restricted' ? '受限' : '正常' }}</span>
    <span
      v-if="status === 'restricted' && until"
      class="status-pill__until"
    >至{{ until }}</span>
  </div>
</template>

<script setup>
  defineProps({
    // 'normal' | 'restricted'
    status: {
      type: String,
      default: "normal"
    },
    // 受限到期日字串，例如 "2026/9/12"
    until: {
      type: String,
      default: ""
    }
  });
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .status-pill__text {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 12px;
    border-radius: 100px;
    font-size: map-get($fontSize, hint);
    white-space: nowrap;
  }

  .status-pill.normal .status-pill__text {
    color: map-get($color, olive);
    background-color: map-get($color, lightGreen);
    border: 1px solid rgba(79, 138, 91, 0.35);
  }

  .status-pill.restricted .status-pill__text {
    color: map-get($color, error);
    background-color: map-get($color, lightRed);
    border: 1px solid rgba(230, 57, 70, 0.35);
  }

  .status-pill__until {
    font-size: map-get($fontSize, hint);
    color: map-get($color, hint);
    white-space: nowrap;
  }
</style>
