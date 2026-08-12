import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import prodMsgList from "@/components/prodMsgList.vue";

createApp(Header, {
   solid: true
}).mount("#headerApp");
createApp(Footer).mount("#footerApp");
// 留言 List
createApp(prodMsgList).mount('#msginfoApp');

// == 輪播圖 =================================================
// slick 初始化 + 箭頭 / 縮圖 邏輯
$(document).ready(function () {

   $('.slider-for').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: true,
      dots: true,
      appendArrows: $('.gallery-wrap'), // 箭頭掛到 gallery-wrap，脫離圖片本身
      prevArrow: '<button type="button" class="slick-prev"><svg viewBox="0 0 24 24" fill="none"><path d="M15 5 L8 12 L15 19" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
      nextArrow: '<button type="button" class="slick-next"><svg viewBox="0 0 24 24" fill="none"><path d="M9 5 L16 12 L9 19" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'
   });

   // 縮圖列：純靜態排列，不是 slick slider，不會捲動
   const $thumbs = $('.slider-nav .thumb');

   $thumbs.on('click', function () {
      const index = $(this).data('index');
      $('.slider-for').slick('slickGoTo', index);
   });

   // 不管用箭頭、滑動、還是點縮圖切換，afterChange 都會觸發
   // 統一在這裡更新縮圖的 active border
   $('.slider-for').on('afterChange', function (event, slick, currentSlide) {
      $thumbs.removeClass('is-active')
         .filter(`[data-index="${currentSlide}"]`)
         .addClass('is-active');
   });

});


// 燈箱
const addMsg = document.getElementById('Btn-addMsg');
const MsgModal = document.querySelector('.example-modal');
const btnClose = document.querySelector('.btn-close');
const mask = document.querySelector('.modal-mask');



// console.log(forminput[0].values);
// 開啟燈箱
addMsg.addEventListener('click', () => {
   document.body.style.overflow = 'hidden';
   MsgModal.classList.add('is-open');
});

// 關閉燈箱
function closeMsgModal() {
   document.body.style.overflow = '';
   MsgModal.classList.remove('is-open');
}
btnClose.addEventListener('click', () => {
   closeMsgModal();
});
mask.addEventListener('click', () => {
   closeMsgModal();
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

      handleFormSuccess();
   }


});

// 假設表單送出成功後觸發這個函式
function handleFormSuccess() {
   // 1. 關閉彈窗
   closeMsgModal()

   // 2. 清空彈窗內的表單內容（避免下次開啟時留有上次的資料）
   const form = document.querySelector('#msgForm');
   form.reset();

   // 3. 跳出輕量的成功提示（如 Toast 或原生 alert）
   alert('交換申請已成功送出！');

   // 4. (選填) 即時更新商品頁上的按鈕狀態（讓 UX 更好）
   const btnExchange = document.getElementById('Btn-addMsg');
   btnExchange.textContent = '已送出交換申請';
   btnExchange.disabled = true; // 避免重複點擊
}

// == 置頂按鈕 ===================================
const prodHeader = document.getElementById('prodDetail');
const backToTopBtn = document.getElementById("backToTopBtn"); // 置頂按鈕

//置頂函式
function handleBackToTop() {
   //取得約戰列表區離瀏覽器畫面頂部的距離
   const cardAreaTop = prodHeader.getBoundingClientRect().top;

   //當距離<0，代表畫面已經捲動到列表區塊，這時才顯示出現置頂按鈕；設定-200，這樣當使用者已經在列表區往下捲動200px後，才出現置頂按鈕
   if (cardAreaTop < -200) {
      backToTopBtn.classList.add("is-show");
   } else {
      backToTopBtn.classList.remove("is-show");
   }
}

window.addEventListener("scroll", handleBackToTop);

// 點擊按鈕後回到約戰列表頂部
backToTopBtn.addEventListener("click", function () {
   prodHeader.scrollIntoView({
      behavior: "smooth",
      block: "start" //讓battleCardArea的頂端對齊畫面頂端
   });
});


// == 留言排序 =====================================================
document.querySelectorAll('.icon-sort [data-sort]').forEach(btn => {
   btn.addEventListener('click', () => {
      window.dispatchEvent(new CustomEvent('msg-sort-change', {
         detail: { sortOrder: btn.dataset.sort }
      }));
   });
});