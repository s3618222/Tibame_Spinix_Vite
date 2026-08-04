// header logo 旋轉效果
// const header = document.querySelector('header');
const header = document.querySelector('#headerApp');

function changeHeader() {
    if (window.scrollY > 40) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
}
window.addEventListener("scroll", changeHeader);
