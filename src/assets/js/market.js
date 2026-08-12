import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import Pagination from "@/components/pagination.vue";
import ElementPlus from "element-plus";
import ProductList from "@/components/ProductList.vue";

createApp(Header).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(Pagination, {
   currentPage: 1,
   pageSize: 20,
   total: 5
}).use(ElementPlus).mount("#paginationApp");

createApp(ProductList).mount("#exchangeApp");


// == 篩選列 =====================================================
const taiwanDistricts = {
   '基隆市': ['仁愛區', '信義區', '中正區', '中山區', '安樂區', '暖暖區', '七堵區'],
   '台北市': ['中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區'],
   '新北市': ['板橋區', '三重區', '中和區', '永和區', '新莊區', '新店區', '樹林區', '鶯歌區', '三峽區', '淡水區'],
};

const form = document.querySelector('#marketFilter form');
const typeSelect = form.querySelector('[name="type"]');
const selectCity = document.getElementById('select-city');
const selectDistrict = document.getElementById('select-district');

let sortOrder = 'newest';

function broadcastFilters() {
   window.dispatchEvent(new CustomEvent('filter-change', {
      detail: {
         type: typeSelect.value,
         city: selectCity.value,
         district: selectDistrict.value,
         sortOrder
      }
   }));
}

selectCity.addEventListener('change', function () {
   const city = selectCity.value;
   const districts = taiwanDistricts[city] || []; // 問題1修正:city 為空字串時 fallback 成空陣列
   selectDistrict.innerHTML = '<option value="">請選擇行政區</option>';

   districts.forEach(district => {
      const option = document.createElement('option');
      option.value = district;
      option.textContent = district;
      selectDistrict.appendChild(option);
   });

   broadcastFilters();
});

typeSelect.addEventListener('change', broadcastFilters);
selectDistrict.addEventListener('change', broadcastFilters);

form.querySelectorAll('[data-sort]').forEach(btn => {
   btn.addEventListener('click', () => {
      sortOrder = btn.dataset.sort;
      broadcastFilters();
   });
});

form.querySelectorAll('.btn-reset').forEach(btn => {
   btn.addEventListener('click', (e) => {
      e.preventDefault(); // 問題3修正:避免原生 reset 行為干擾自訂重置邏輯
      typeSelect.value = 'all';
      selectCity.value = '';
      selectDistrict.innerHTML = '<option value="">請選擇行政區</option>';
      sortOrder = 'newest';
      broadcastFilters();
   });
});

// 問題2修正:module script 執行時機等同 defer,DOMContentLoaded 可能已經錯過,直接呼叫即可
broadcastFilters();