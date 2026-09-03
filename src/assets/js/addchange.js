import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import upload from "@/components/uploadImg.vue";
import { initCityDistrictSelector } from './citySelector.js';

createApp(Header, {
   solid: true
}).mount("#headerApp");
createApp(Footer).mount("#footerApp");

//重要!!! 判斷當前環境
const phpBaseUrl =
   location.hostname === "localhost" ||
      location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : `${location.origin}/ckd101/g2/php`;

const uploadImgVm = createApp(upload).mount('#uploadImgApp');
window.photoUploadInstance = uploadImgVm;

// 進頁即檢查交換功能是否被停權，受限則擋下並導回 market
function checkCreateExchangeAccess() {
   fetch(`${phpBaseUrl}/exchange/exchange_access_get.php`, {
      credentials: "include"
   }).then(res => res.json()).then(data => {

      // 未能成功確認權限時，先直接導回交換市集
      if (!data.success) {
         alert(data.message || "目前無法確認二手交換功能狀態");
         window.location.href = `${import.meta.env.BASE_URL}market.html`;
         return;
      }

      // 交換功能受限時，禁止進入刊登頁
      if (!data.allowed) {
         if (data.status === "TEMP-RESTRICT") {
            alert("你的二手交換功能目前暫時受限，受限期間無法刊登交換。");
         } else if (data.status === "PERMA-RESTRICT") {
            alert("你的二手交換功能目前已被限制使用，無法刊登交換。");
         } else {
            alert("目前無法使用二手交換相關功能。");
         }
         window.location.href = `${import.meta.env.BASE_URL}market.html`;
         return;
      }
   }).catch(error => {
      console.error("二手交換功能權限確認失敗：", error);
   });
}
checkCreateExchangeAccess();

const selectCity = document.getElementById('select-city');
const selectDistrict = document.getElementById('select-district');

initCityDistrictSelector(selectCity, selectDistrict);

// == 取消按鈕 ==========================================
const btnReset = document.querySelector('.btnNoFill');
const form = document.querySelector('form');
const btnSubmit = document.querySelector('button[type="submit"].btnFill');


btnReset.addEventListener('click', (e) => {
   e.preventDefault();
   if (isFormInvalid()) {
      window.alert('請先填入資料');
      return;
   }

   const isConfirm = window.confirm('是否全部清空?');
   if (isConfirm) {
      form.reset();

      if (window.photoUploadInstance && typeof window.photoUploadInstance.clearFiles === 'function') {
         window.photoUploadInstance.clearFiles();
      } else {
         console.warn('找不到圖片上傳元件的 clearFiles 方法');
      }
   }
});

function isFormInvalid() {

   // 1. 檢查一般輸入框 (text, select, textarea)
   const standardFields = document.querySelectorAll(
      'input:not([type="radio"]):not([type="checkbox"]), select, textarea'
   );

   // 只要有「任何一個」一般欄位是空的，就回傳 true（維持原本邏輯，全部都空才算空）
   const hasEmptyStandard = [...standardFields].every(
      input => input.value.trim() === ''
   );

   let hasEmptyPhoto = true;
   if (window.photoUploadInstance && typeof window.photoUploadInstance.getFiles === 'function') {
      const photos = window.photoUploadInstance.getFiles();
      hasEmptyPhoto = !photos || photos.length === 0;
   }
   // 3. 檢查檔案上傳欄位：要「全部」檔案欄位都沒選檔案，才算空
   const fileFields = document.querySelectorAll('input[type="file"]');
   const hasEmptyFile = fileFields.length === 0 || Array.from(fileFields).every(
      field => !field.files || field.files.length === 0
   );

   // 只有當「一般欄位、檔案」三者全部都空，表單才算完全無效
   return hasEmptyStandard && hasEmptyFile && hasEmptyPhoto;
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
      // 表單驗證
      const addChangeForm = new FormData(document.getElementById('form-addchange'));

      if (window.photoUploadInstance) {
         const files = window.photoUploadInstance.getFiles();
         files.forEach((file) => {
            if (file) {
               addChangeForm.append('photos[]', file);
            }
         });
      } else {
         console.warn('找不到圖片上傳元件實例');
      }

      fetch(`${phpBaseUrl}/exchange/add_change.php`, {
         method: 'POST',
         body: addChangeForm,
         credentials: 'include'
      }).then(res => res.json()).then(data => {
         if (data.success) {
            alert('成功刊登');
            if (document.referrer && document.referrer.includes(window.location.host)) {
               location.href = "market.html";
            } else {
               window.location.href = 'homepage.html';
            }
         } else {
            alert(data.message || '刊登失敗');
         }
      })
   }
});






