<template>
   <div class="container">
      <ProductCard
         v-for="item in filteredCards"
         :key="item.post_id"
         :post_id="item.post_id"
         :title="item.title"
         :image="item.product_img"
         :avatar="item.headshot"
         :username="item.name"
         :create_time="item.create_time"
         :city="item.city"
         :district="item.district"
         :state="statusLabelMap[item.status]"
      />
      <p v-if="filteredCards.length === 0" class="empty-state">
         查無符合條件的商品
      </p>
   </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ProductCard from './productCard.vue';
import { exchangeList, statusLabelMap } from '@/data/mockExchangeData.js';   //  改用共用資料

const ExChangeInfo = ref(exchangeList);   // 不用自己重複寫一份

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

      // 只顯示「可交換」狀態的商品，這是公開頁面的規則
      const matchStatus = item.status === 'available';

      const matchType = f.type === 'all' || item.type === f.type;
      const matchCity = !f.city || item.city === f.city;
      const matchDistrict = !f.district || item.district === f.district;
      return matchStatus && matchType && matchCity && matchDistrict;
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