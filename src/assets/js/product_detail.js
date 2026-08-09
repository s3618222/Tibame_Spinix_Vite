import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";

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
