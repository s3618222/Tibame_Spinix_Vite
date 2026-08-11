<template>
  <div class="part-grid">
    <!-- <PartCard/> -->
    <PartCard v-for="item in partList" :key="item.id" :part="item"></PartCard>
  </div>
</template>

<script>
import PartCard from '@/components/build/partCard.vue';

export default {
  name: "PartGrid",

  components: {
    PartCard
  },

  data(){
    return {
      
      partList: [
        {
          id: 1,
          name: "蒼龍突擊",
          image: `build/blade/BX-49 蒼龍突擊.png`,
          type: "blade"
        },
        {
          id: 2,
          name: "衝擊龍神",
          image: `build/blade/BX-50-03 衝擊龍神.png`,
          type: "blade"
        },
        {
          id: 1,
          name: "暴風天馬",
          image: `build/blade/BXG-47 暴風天馬.png`,
          type: "blade"
        },
        {
          id: 1,
          name: "蒼龍神劍",
          image: `build/blade/BXG-49 蒼龍神劍.png`,
          type: "blade"
        }
      ]
    }
  }
}

</script>

<style lang="scss" scoped>
.part-grid {
  width: 100%;
  display: grid;
  gap: 12px;
  background: #fff;
  padding: 20px;
  border-radius: 0 0 16px 16px;

  /* 預設（手機版）：2 欄卡片 */
  grid-template-columns: repeat(2, 1fr);

  /* 電腦版 (>= 992px)：4 欄卡片 */
  @media (min-width: 992px) {
    grid-template-columns: repeat(4, 1fr);
  }
}
</style>