<template>
  <div class="part-grid">
    <!-- <PartCard/> -->
    <PartCard
      v-for="item in parts"
      :key="item.id"
      :part="item"
      :selected-id="selectedId"
      @select="forwardSelect"
    ></PartCard>
  </div>
</template>

<script>
import PartCard from '@/components/build/partCard.vue';

export default {
  name: "PartGrid",

  components: {
    PartCard
  },

  props: {
    parts: { type: Array, required: true },
    selectedId: { type: Number, default: null }
  },

  methods: {
    forwardSelect(part) {
      this.$emit('select-part', part);
    }
  }
}

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.part-grid {
  width: 100%;
  display: grid;
  gap: 12px;
  background: #fff;
  padding: 20px;
  border-radius: 0 0 16px 16px;

  /* 預設（手機版）：2 欄卡片 */
  grid-template-columns: repeat(2, 1fr);
  
  @include rwd("desktop") {
    grid-template-columns: repeat(3, 1fr);
    
  }

  /* 電腦版 (>= 992px)：4 欄卡片 */
  @include rwd("1200px") {
    grid-template-columns: repeat(4, 1fr);
  }
}
</style>