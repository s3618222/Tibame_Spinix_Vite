import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";

createApp(Header).mount("#headerApp");
createApp(Footer).mount("#footerApp");


// 判斷視窗寬度
// const width = window.innerWidth;
// console.log(width);
// const header = document.getElementById('headerApp');
// const footer = document.getElementById('footerApp');
// const heroBtn = document.getElementById('btn-hero');
// if (width < 768) {
//    // heroBtn.style.display = 'none';
//    header.style.display = 'none';
//    footer.style.display = 'none';
// } else if (width >= 768) {
//    // heroBtn.style.display = 'block';
//    header.style.display = 'block';
//    footer.style.display = 'block';
// }