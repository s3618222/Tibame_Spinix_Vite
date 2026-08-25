<template>
  <button type="button" class="btnNoFill" @click="$emit('click')">
    <!-- 預設文字 -->
    <slot>顯示更多</slot> 
  </button>
</template>

<script>
export default {
  name: "LoadMoreButton",
  emits: ["click"]
}
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

</style>