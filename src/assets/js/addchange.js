import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import upload from "@/components/uploadImg.vue";

createApp(Header, {
   solid: true
}).mount("#headerApp");
createApp(Footer).mount("#footerApp");

createApp(upload).mount('#uploadImgApp');

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


// == 取消按鈕 ==========================================
const btnReset = document.querySelector('.btnNoFill');
const form = document.querySelector('form');
const btnSubmit = document.querySelector('button[type="submit"].btnFill');

// console.log(btnSubmit);
btnReset.addEventListener('click', (e) => {
   e.preventDefault();
   if (isFormInvalid()) {
      window.alert('請先填入資料');
      return;
   }

   const isConfirm = window.confirm('是否全部清空?');
   if (isConfirm) {
      form.reset();
   } else {
      return;
   }
});

function isFormInvalid() {
   // 1. 檢查一般輸入框 (text, select, textarea)
   // 排除 radio 與 checkbox，因為它們需要靠 checked 判斷
   const standardFields = document.querySelectorAll(
      'input:not([type="radio"]):not([type="checkbox"]), select, textarea'
   );

   // 只要有「任何一個」一般欄位是空的，就回傳 true
   const hasEmptyStandard = [...standardFields].every(
      input => input.value.trim() === ''
   );

   // 2. 檢查 Radio 群組
   const requiredRadios = document.querySelectorAll('input[type="radio"]');
   // 取得所有 required radio 的 name（用 Set 去除重複的 name）
   const radioNames = [...new Set([...requiredRadios].map(r => r.name))];

   // 檢查是否「有任何一個 Radio 群組完全沒有被勾選」
   const hasEmptyRadioGroup = radioNames.some(name => {
      const isChecked = document.querySelector(`input[type="radio"][name="${name}"]:checked`);
      return !isChecked;
   });

   // 只要一般欄位有空，或 Radio 有空，就代表表單無效 (Has Empty)
   return hasEmptyStandard || hasEmptyRadioGroup;
}



// == 送出按鈕 =====================================
btnSubmit.addEventListener('click', (e) => {
   e.preventDefault(); // 1. 先阻擋表單預設送出

   // 2. 抓取所有必填的一般輸入框、下拉選單與多行文字框
   const requiredFields = form.querySelectorAll(
      'input[required]:not([type="radio"]):not([type="checkbox"]), select[required], textarea[required]'
   );

   let hasError = false;
   let firstErrorField = null;


   // 3. 逐一檢查是否空白
   requiredFields.forEach(field => {
      const isEmpty = field.value.trim() === '';
      if (isEmpty) {
         field.classList.add('-isError'); // 空白則加上紅框
         hasError = true;

         // 紀錄第一個出錯的欄位，等一下將焦點移過去
         if (!firstErrorField) firstErrorField = field;
      } else {
         field.classList.remove('-isError'); // 有填寫則移除紅框
      }

      // 4. 【高 UX 細節】當使用者開始輸入/修改時，自動把紅框移除
      field.addEventListener('input', () => {
         if (field.value.trim() !== '') {
            field.classList.remove('-isError');
         }
      });
   });

   // 5. 判斷驗證結果
   if (hasError) {
      window.alert('星號*為必填項目');
      // 將游標自動聚焦到第一個沒填的欄位（體貼使用者的操作體驗）
      firstErrorField?.focus();
      return;
   } else {
      window.alert('送出成功');
   }

   if (document.referrer && document.referrer.includes(window.location.host)) {
      window.history.back(); // 回到進表單前的那一頁
   } else {
      window.location.href = '/'; // 沒上一頁紀錄時的預設首頁
   }

});
