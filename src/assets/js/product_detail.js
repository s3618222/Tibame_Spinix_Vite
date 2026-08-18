import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import prodDetail from '@/components/ProdDetail.vue';

createApp(prodDetail).mount('#proddetailApp');

createApp(Header, {
   solid: true
}).mount("#headerApp");
createApp(Footer).mount("#footerApp");

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