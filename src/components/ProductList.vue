<template>
   <div class="container">
      <ProductCard
         v-for="item in ExChangeInfo"
         :key="item.id"
         :id="item.id"
         :title="item.title"
         :image="item.product_img"
         :avatar="item.headshot"
         :username="item.name"
         :postDate="item.date"
         :city="item.city"
         :district="item.district"
         :state="item.state"
      />
   </div>
</template>

<script setup>
import { ref } from 'vue'
import ProductCard from './productCard.vue'

const ExChangeInfo = ref([
   {
      id: 1,
      type: 'beyblade',
      product_img: '/CX13_01.webp',
      title: '龍王閃擊',
      headshot: '/spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   },
   {
      id: 2,
      type: 'blade',
      product_img: 'BX_02.webp',
      title: '蒼龍突擊',
      headshot: '/spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   },
   {
      id: 3,
      type: 'ratchet',
      product_img: '/BX23_01.webp',
      title: '鳳凰飛翼',
      headshot: '/spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   },
   {
      id: 4,
      type: 'bit',
      product_img: '/CX02_01.webp',
      title: '魔導致尊',
      headshot: '/spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   },
   {
      id: 5,
      type: 'other',
      product_img: '/BX-28.webp',
      title: '旋風發射器 白色版',
      headshot: '/spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   }
]);
</script>

<style lang="scss" scoped>
.container {
   display: grid;
   grid-template-columns: repeat(2, 1fr);
   gap: 20px;
   padding-inline:16px ;
}

@media screen and (width >= 768px) {
   .container {
      grid-template-columns: repeat(3, 1fr);
   }
}

@media screen and (width >= 992px) {
   .container {
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
   }
}
</style>