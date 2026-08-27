import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import Pagination from "@/components/pagination.vue";
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";
import ProductList from "@/components/ProductList.vue";
import { initCityDistrictSelector } from './citySelector.js';

createApp(Header).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(Pagination, {
   currentPage: 1,
   pageSize: 20,
   total: 20
}).use(ElementPlus).mount("#paginationApp");

createApp(ProductList).mount("#exchangeApp");




// == 篩選列 =====================================================

const form = document.querySelector('#marketFilter form');
const typeSelect = form.querySelector('[name="type"]');
const selectCity = document.getElementById('select-city');
const selectDistrict = document.getElementById('select-district');

console.log(selectCity);

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

initCityDistrictSelector(selectCity, selectDistrict, broadcastFilters);

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

// 目前登入會員
let currentMember = null; //存取當前登入者資訊

const phpBaseUrl =
   location.hostname === "localhost" ||
      location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : `${location.origin}/ckd101/g2/php`;

function fetchCurrentMember() {
   return fetch(`${phpBaseUrl}/member/currentMember_get.php`, {
      credentials: "include"
   }).then(res => res.json()).then(data => {

      if (data.success && data.isLoggedIn) { //已有登入會員時
         currentMember = data.member;
      } else { //未登入時
         currentMember = null;
      }

      // console.log("目前登入會員：", currentMember);
   });
}

const addExchange = document.querySelectorAll('.gotoaddChange');

addExchange.forEach((item, index) => {
   // console.log(item);
   item.addEventListener('click', function (e) {
      e.preventDefault();

      if (currentMember === null) {
         window.alert('請先登入會員');
         window.location.href = `${import.meta.env.BASE_URL}signIn.html`;
      } else {
         window.location.href = `addChange.html`;
      }
   })
});


fetchCurrentMember();


