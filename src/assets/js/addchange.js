import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import upload from "@/components/uploadImg.vue";

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

// createApp(upload).mount('#uploadImgApp');
const uploadImgVm = createApp(upload).mount('#uploadImgApp');
window.photoUploadInstance = uploadImgVm;

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

   // 3. 檢查檔案上傳欄位（如果該欄位是必填，沒選檔案就算空）
   const fileFields = document.querySelectorAll('input[type="file"]');
   const hasEmptyFile = Array.from(fileFields).some(
      field => !field.files || field.files.length === 0
   );

   // 只要一般欄位有空，或 Radio 有空，就代表表單無效 (Has Empty)
   return hasEmptyStandard || hasEmptyRadioGroup || hasEmptyFile;
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
               window.history.back();
            } else {
               window.location.href = 'homepage.html';
            }
         } else {
            alert('錯誤');
         }
      })
   }
});






