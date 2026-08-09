// == 排序 (最新、最舊)============================================
// == 排序按鈕預設 ===============================================
const DEFAULT_SORT = 'newest';
let currentSort = DEFAULT_SORT;

const allSortButtons = document.querySelectorAll('[data-sort]');

function applySort(order) {
   currentSort = order;
   updateButtonStyles();
}

function updateButtonStyles() {
   allSortButtons.forEach(btn => {
      btn.classList.toggle('-select', btn.dataset.sort === currentSort);
   });
}

allSortButtons.forEach(btn => {
   btn.addEventListener('click', () => {
      applySort(btn.dataset.sort);
   });

});

let btnReset = document.querySelectorAll('.btn-reset');
btnReset.forEach(btn => {
   btn.addEventListener('click', () => {
      applySort(DEFAULT_SORT);
   })
});

updateButtonStyles();