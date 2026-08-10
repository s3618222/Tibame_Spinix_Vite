import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import { el } from "element-plus/es/locales.mjs";

createApp(Header, {
   solid: true
}).mount("#headerApp");
createApp(Footer).mount("#footerApp");

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
const btnReset = document.querySelector('.btnNoFill');
const form = document.getElementById('msgForm');
const btnSubmit = document.querySelector('button[type="submit"].btnFill');
let requiredInputs = document.querySelectorAll('[required]');

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

// 燈箱取消按鈕
btnReset.addEventListener('click', (e) => {
   e.preventDefault();

   // 是否為空字串
   let hasEmpty = [...requiredInputs].every(input => input.value.trim() === '');


   if (hasEmpty) {
      window.alert('請輸入內容');
      return;
   }

   const isConfirm = window.confirm('是否全部清空?');
   if (isConfirm) {
      form.reset();
   } else {
      return;
   }
});

// 送出按鈕
btnSubmit.addEventListener('click', () => {
   requiredInputs.forEach(input => {
      if (input.value.trim() === '') {
         input.classList.add('-isError');
      } else {
         input.classList.remove('-isError');
      }
   });

});

requiredInputs.forEach(input => {
   input.addEventListener('blur', () => {
      if (input.value.trim() !== '') {
         input.classList.remove('-isError');
      } else {
         input.classList.add('-isError'); // 離開時還是空的，繼續顯示紅框
      }
   });
});

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

