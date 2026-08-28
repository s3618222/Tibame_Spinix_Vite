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
   
   const cardAreaTop = prodHeader.getBoundingClientRect().top;

   if (cardAreaTop < -200) {
      backToTopBtn.classList.add("is-show");
   } else {
      backToTopBtn.classList.remove("is-show");
   }
}

window.addEventListener("scroll", handleBackToTop);


backToTopBtn.addEventListener("click", function () {
   prodHeader.scrollIntoView({
      behavior: "smooth",
      block: "start" 
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