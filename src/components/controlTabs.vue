<!-- StatusTabs.vue -->
<template>
  <div class="status-tabs">
    <button
      v-for="tab in tabs"
      :key="tab.value"
      type="button"
      class="status-tab"
      :class="{ active: modelValue === tab.value }"
      @click="handleClick(tab.value)"
    >
      {{ tab.label }} <span>({{ tab.count }})</span>
    </button>
  </div>
</template>

<script>
export default {
  name: "StatusTabs",

  props: {
    // 標籤陣列，格式：[{ label, value, count }]
    tabs: {
      type: Array,
      required: true
    },
    // 目前選中的值（搭配 v-model 使用）
    modelValue: {
      type: String,
      default: ""
    }
  },

  emits: ["update:modelValue"],

  methods: {
    handleClick(value) {
      this.$emit("update:modelValue", value);
    }
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/_var' as *;
.status-tabs {
  display: flex;
  gap: 40px;
  padding-bottom: 12px;
  border-bottom: 1px solid map-get($color, hint );

  .status-tab {
    font-size: 18px;
    color: map-get($color , hint );
    transition: color 0.28s ease;
    

    span {
      color: #bbb;
      font-size: 18px;
    }

    &.active {
      color: map-get($color, secondary );

      span {
        color: map-get($color, secondary );
      }
    }

    &:hover:not(.active) {
      color: #666;
    }
  }
}
</style>