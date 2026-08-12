<template>
   <div class="container">
      <ProductCard
         v-for="item in filteredCards"
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
      <p v-if="filteredCards.length === 0" class="empty-state">
         查無符合條件的商品
      </p>
   </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ProductCard from './productCard.vue';

const ExChangeInfo = ref([
   {
      id: 1,
      type: 'beyblade',
      product_img: 'CX13_01.webp',
      title: '龍王閃擊',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-07-26',
      city: '基隆市',
      district: '信義區',
      state: '可交換'
   },
   {
      id: 2,
      type: 'blade',
      product_img: 'BX_02.webp',
      title: '蒼龍突擊',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   },
   {
      id: 3,
      type: 'ratchet',
      product_img: 'BX23_01.webp',
      title: '鳳凰飛翼',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-07-06',
      city: '新北市',
      district: '板橋區',
      state: '可交換'
   },
   {
      id: 4,
      type: 'bit',
      product_img: 'CX02_01.webp',
      title: '魔導致尊',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-12',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   },
   {
      id: 5,
      type: 'other',
      product_img: 'BX-28.webp',
      title: '旋風發射器 白色版',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-13',
      city: '台北市',
      district: '中正區',
      state: '可交換'
   }
]);

const currentFilters = ref({
   type: 'all',
   city: '',
   district: '',
   sortOrder: 'newest'
});

function handleFilterChange(e) {
   currentFilters.value = e.detail;
}

onMounted(() => {
   // 保險機制:主動讀一次 DOM 現值,避免漏接 main.js 的初始廣播
   const form = document.querySelector('#marketFilter form');
   if (form) {
      const typeEl = form.querySelector('[name="type"]');
      const cityEl = document.getElementById('select-city');
      const districtEl = document.getElementById('select-district');
      currentFilters.value = {
         type: typeEl ? typeEl.value : 'all',
         city: cityEl ? cityEl.value : '',
         district: districtEl ? districtEl.value : '',
         sortOrder: 'newest'
      };
   }
   window.addEventListener('filter-change', handleFilterChange);
});

onUnmounted(() => {
   window.removeEventListener('filter-change', handleFilterChange);
});

const filteredCards = computed(() => {
   let result = ExChangeInfo.value.filter(item => {
      const f = currentFilters.value;
      const matchType = f.type === 'all' || item.type === f.type;
      const matchCity = !f.city || item.city === f.city;
      const matchDistrict = !f.district || item.district === f.district;
      return matchType && matchCity && matchDistrict;
   });

   result = [...result].sort((a, b) => {
      const dateA = new Date(a.date);
      const dateB = new Date(b.date);
      return currentFilters.value.sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
   });

   return result;
});
</script>

<style lang="scss" scoped>
.container {
   display: grid;
   grid-template-columns: repeat(2, 1fr);
   gap: 20px;
   padding-inline: 16px;
}

.empty-state {
   grid-column: 1 / -1;
   text-align: center;
   padding: 40px 0;
   color: #999;
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