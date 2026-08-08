import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
// 分頁器
import Pagination from "@/components/pagination.vue";
import ElementPlus from "element-plus";
// 商品卡
import ProductList from "@/components/ProductList.vue";


createApp(Header).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(Pagination, {
   currentPage: 1,
   pageSize: 20,
   total: 5
}).use(ElementPlus).mount("#paginationApp");

// 商品列表掛載
createApp(ProductList).mount("#exchangeApp");


// == 篩選列 =====================================================
// == 排序按鈕預設 ===============================================
const DEFAULT_SORT = 'newest';
let currentSort = DEFAULT_SORT;

const allSortButtons = document.querySelectorAll('[data-sort]');

function applySort(order) {
   currentSort = order;
   updateButtonStyles();
}

function updateButtonStyles() {
   allSortButtons.forEach(btn => {
      btn.classList.toggle('-select', btn.dataset.sort === currentSort);
   });
}

allSortButtons.forEach(btn => {
   btn.addEventListener('click', () => {
      applySort(btn.dataset.sort);
   });

});

let btnReset = document.querySelectorAll('.btn-reset');
btnReset.forEach(btn => {
   btn.addEventListener('click', () => {
      applySort(DEFAULT_SORT);
   })
});

updateButtonStyles();

// == 縣市篩選 ======================================
const taiwanDistricts = {
   '基隆市': ['仁愛區', '信義區', '中正區', '中山區', '安樂區', '暖暖區', '七堵區'],
   '台北市': ['中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區'],
   '新北市': ['板橋區', '三重區', '中和區', '永和區', '新莊區', '新店區', '樹林區', '鶯歌區', '三峽區', '淡水區'],
};

const selectCity = document.getElementById('select-city');
const selectDistrict = document.getElementById('select-district');

selectCity.addEventListener('change', function () {
   let city = selectCity.value;
   // console.log(city);
   let district = taiwanDistricts[city];
   // console.log(district);
   district.forEach(district => {
      const option = document.createElement('option');
      option.value = district;
      option.textContent = district;
      selectDistrict.appendChild(option);
   });
});

