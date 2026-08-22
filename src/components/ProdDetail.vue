<template>
  <h1>{{ pageTitle }}</h1>
  <a class="back-page" :href="backLink">返回</a>

  <div class="container" v-if="article">
    <!-- 商品圖片 -->
    <div class="panel" >
      <div v-if="!isEditing">
        <div class="gallery-wrap">
          <Carousel v-model="activeImageIndex" :items-to-show="1" :wrap-around="true">
            <Slide v-for="(img, index) in galleryImages" :key="index">
              <div class="img-big">
                <img :src="img" alt="">
              </div>
            </Slide>

            <template #addons>
              <Navigation />
              <Pagination />
            </template>
          </Carousel>
      </div>

      <div class="slider-nav">
        <div
          v-for="(img, index) in galleryImages"
          :key="index"
          class="thumb"
          :class="{ '--active': index === activeImageIndex }"
          @click="activeImageIndex = index"
        >
          <img :src="img" alt="">
        </div>
      </div>
      </div>
        <!-- 編輯模式下顯示照片上傳元件 -->
      <PhotoUploader v-else v-model="editForm.images" />
    </div>
  

    <!-- 商品資訊 -->
    <div class="prod-info">
      <div class="prod-info-header form-label-style">
        <p class="title" v-if="!isEditing">{{ article.title }}</p>
        <input
          v-else
          type="text"
          class="title-input"
          v-model="editForm.title"
        >

      <!-- 自己刊登的文章：顯示編輯按鈕 -->
        <div class="owner-actions" v-if="isOwner">
          <template v-if="!isEditing">
            <button type="button" class="btn-edit" @click="startEdit">
              修改資訊
              <i class="fa-regular fa-pen-to-square"></i>
            </button>
          </template>

          <template v-else>
            <button type="button" class="btn-edit" @click="saveEdit">儲存</button>
            <button type="button" class="btn-edit" @click="cancelEdit">取消</button>
          </template>
        </div>
      </div>
      

      <div class="tags form-label-style">
        <span class="chip chip--exchangeable">{{ statusLabelMap[article.status] }}</span>
        <span class="chip chip--state" v-if="!isEditing">{{ conditionLabelMap[article.condition] }}</span>
        <select
          v-else
          v-model="editForm.condition"
        >
          <option value="new">全新</option>
          <option value="good">二手-良好</option>
          <option value="fair">二手-有使用痕跡</option>
        </select>
        <span class="chip chip--category" v-if="!isEditing">{{ typeLabelMap[article.type] }}</span>
        <select
          v-else
          class="title-input"
          v-model="editForm.type"
        >
          <option value="beyblade">陀螺本體</option>
          <option value="blade">戰刃</option>
          <option value="ratchet">固鎖</option>
          <option value="bit">軸心</option>
          <option value="others">其他</option>
        </select>
      </div>

      <div class="user box-style">
        <div class="img-user">
            <img :src="`./${article.headshot}`" alt="">
          </div>
        <div class="user-info">
          <div class="info-header">
            <p class="user-name">{{ article.name }}</p>
            <a href="complaint.html" target="_blank">
              <i class="fa-solid fa-triangle-exclamation" v-if="!isOwner"></i>
            </a>
          </div>
          <!-- 顯示會員資訊 -->
          <div :class="{'topLine':article.status === 'exchanging'}">
            <ContactDrawer
              :contact="article.post_contact"
              :show="article.status === 'exchanging'"
            />
          </div>
        </div>
      </div>

      <div class="prod-txt box-style form-label-style">
        <p class="title">物品描述</p>
        <p class="prod-content"  v-if="!isEditing">{{ article.description}}</p>
        <textarea
          v-else
          class="prod-content-input"
          v-model="editForm.description"
          rows="5"
        ></textarea>
      </div>
      <!-- 希望換到物品 -->
      <div class="prod-txt box-style form-label-style">
        <p class="title">希望換到的物品</p>
        <p
          class="prod-content"
          :class="{ 'empty-state': !article.want_item?.trim() }"
          v-if="!isEditing"
        >
          {{ article.want_item?.trim() ? article.want_item : `版主目前沒有特別想要的物品哦 ~ 趕快去留言想交換出的陀螺吧   !` }}
        </p>
        <textarea
          v-else
          class="prod-content-input"
          v-model="editForm.want_item"
          rows="3"
          placeholder="若沒有特別想要的物品，可留空"
        ></textarea>
      </div>
      
      <!-- 被申訴時警告訊息 -->
      <WarningBanner
        v-if="article.remove_reason !== ''"
        title= "文章"
        :remove_reason = "article.remove_reason"
        :show_contact="isSeller"
      />
      


      <!-- 情況一：賣家，還沒選定對象 -->
      <p v-if="isOwner && article.status === 'available'" class="apply-hint">
        <i class="fa-regular fa-hand-point-down"></i>
        您可於下方查看其他會員的交換提議
        <i class="fa-regular fa-hand-point-down"></i>
      </p>
      <!-- 情況二：買家，還沒申請過 -->
      <button
        v-else-if="!isOwner && !alreadyApplied"
        type="button"
        class="btnFill"
        @click="isModalOpen = true"
      >
        我想交換
      </button>
      <!-- 情況三：買家，已經申請過 -->
      <p v-else-if="!isOwner && alreadyApplied" class="apply-hint">
        你已提出過申請，等待版主的選擇
      </p>

      <div class="message-board box-style">
        <h1>交換留言區</h1>
        <div class="icon-sort">
          <button
            type="button"
            :class="{ '--active': sortOrder === 'newest' }"
            @click="sortOrder = 'newest'"
          >
            最新
          </button>
          <button
            type="button"
            :class="{ '--active': sortOrder === 'oldest' }"
            @click="sortOrder = 'oldest'"
          >
            最舊
          </button>
        </div>

        <div class="msg-list">
          <ul>
            <prodMsgInfo
              v-for="comment in sortedComments"
              :key="comment.comm_id"
              :id="comment.comm_id"
              :image="`./${comment.headshot}`"
              :username="comment.name"
              :postDate="comment.create_time"
              :msgtxt="comment.content"
              :isMyComment="comment.mem_id === currentUserId"
              :isOwner="isOwner"
              :isChoose="comment.is_choose"
              :articleStatus="article.status"
              :postId="article.post_id"
              :posterName="article.name"
              :contact="comment.comm_contact"
              :remove_reason="comment.remove_reason"
              :isShow="comment.is_show"
              @select-applicant="handleSelectApplicant"
            />
          </ul>
          <p v-if="!sortedComments.length" class="empty-state">這裡還很安靜，成為第一個提出交換的人吧！</p>
        </div>
      </div>
    </div>
  </div>

  <p v-else class="empty-msg">找不到這篇文章。</p>

  <!-- 留言表單燈箱 -->
  <div class="example-modal" :class="{ 'is-open': isModalOpen }">
    <div class="modal-mask" @click="closeModal"></div>

    <div id="addMsg">
      <div class="btn-close">
        <button type="button" @click="closeModal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <p class="form-title">留下您的交換提議與聯絡方式</p>

        <div class="name form-label-style">
          <label for="name" class="label-title">您的暱稱</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            :class="{ '-isError': errors.name }"
            data-required
          >
        </div>

        <div class="phone form-label-style">
          <label for="phone" class="label-title">聯絡方式</label>
          <input
            type="tel"
            id="phone"
            v-model="form.phone"
            :class="{ '-isError': errors.phone }"
            data-required
          >
        </div>

        <div class="remark form-label-style">
          <label for="change-item" class="label-title">想交換物品</label>
          <textarea
            id="change-item"
            v-model="form.changeItem"
            :class="{ '-isError': errors.changeItem }"
            data-required
          ></textarea>
        </div>

        <p class="form-hint">當雙方皆確認交換後，聯絡資訊將提供給對方作為聯繫用途。</p>

        <div class="form-btn">
          <button type="button" class="btnNoFill" @click="handleReset">清除</button>
          <button type="submit" class="btnFill">送出</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import prodMsgInfo from '@/components/prodMsgInfo.vue';
import { exchangeList, fakeComments, statusLabelMap, typeLabelMap, conditionLabelMap } from '@/data/mockExchangeData';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';
import PhotoUploader from '@/components/uploadImg.vue';
import ContactDrawer from '@/components/ContactDrawer.vue';
import WarningBanner from '@/components/WarningBanner.vue';

// 目前登入的測試會員（之後接後端時，改成從登入狀態拿）
const currentUserId = 999;

// 讀取網址參數：product_detail.html?id=5&from=myPosts
const urlParams = new URLSearchParams(window.location.search);

const articleId = urlParams.get('id'); 
// const articleId = urlParams.get('post_id'); 
const context = urlParams.get('from') || 'browse';


// 從 exchangeList 陣列中，找出 id 相符的那一筆資料
const article = computed(() =>
  exchangeList.find(item => item.post_id === articleId)
);


// 是否已經申請過（排除賣家自己）
const alreadyApplied = computed(() => {
  if (!article.value || isOwner.value) return false;
  return articleComments.value.some(c => c.mem_id === currentUserId);
});



// 依 context 決定標題文字
const pageTitleMap = {
  browse: '物品詳情',
  myPosts: '我刊登的物品',
  myApplications: '我提出的申請'
};

const pageTitle = computed(() => pageTitleMap[context] || '物品詳情');

// 依 context 決定「返回」要導回哪個頁面
const backLinkMap = {
  browse: 'market.html',
  myPosts: 'member.html#/exchange',
  myApplications: 'member.html#/exchange'
};
const backLink = computed(() => backLinkMap[context] || 'market.html');


// == 圖片輪播 ============================================
const galleryImages = ref([
  'CX02_01.webp',
  'BX23_01.webp',
  'BX_02.webp',
  'CX13_01.webp',
  'BX-28.webp'
]);

// == 編輯模式狀態 ==========================================
const isEditing = ref(false);
const editForm = reactive({
  title: '',
  description: '',
  want_item:'',
  images: []   // 存編輯中的圖片網址（新上傳的會是 blob 網址）
});

function startEdit() {
  // 把目前文章資料帶入編輯表單，作為編輯的起始值
  editForm.title = article.value.title;
  editForm.condition = article.value.condition;
  editForm.type = article.value.type;
  editForm.description = article.value.description;
  editForm.want_item = article.value.want_item ?? '';
  editForm.images = [...galleryImages.value];
  isEditing.value = true;
}

function cancelEdit() {

  // 使用者放棄這次編輯，這時候才需要釋放新增的 blob 網址
  editForm.images.forEach(url => {
    if (url.startsWith('blob:') && !galleryImages.value.includes(url)) {
      URL.revokeObjectURL(url);
    }
  });
  isEditing.value = false;
  // 不需要清空 editForm，反正下次 startEdit 會重新帶入最新資料
}

function saveEdit() {
  if (!editForm.title.trim()) {
    window.alert('物品名稱不能為空');
    return;
  }
  if(!editForm.description.trim()){
    window.alert('物品描述不能為空');
    return;
  }

  // 直接修改 exchangeList 裡對應那筆資料（因為是假資料，先用這種方式模擬儲存）
  article.value.title = editForm.title;
  article.value.type = editForm.type;
  article.value.condition = editForm.condition;
  article.value.description = editForm.description;
  article.value.want_item = editForm.want_item;

  galleryImages.value = [...editForm.images];
  activeImageIndex.value = 0;

  // 之後接後端時，這裡改成：
  // await axios.put(`/api/exchange/${article.value.id}`, {
  //   title: editForm.title,
  //   description: editForm.description,
  //   images: editForm.images
  // });

  isEditing.value = false;
  window.alert('商品資訊已更新！');
}

// // 圖片上傳（假資料階段用本機預覽，之後接後端改成真正上傳）
// function handleImageUpload(event) {
//   const files = Array.from(event.target.files);
//   const newImageUrls = files.map(file => URL.createObjectURL(file));

//   editForm.images = [...editForm.images, ...newImageUrls];

//   // 讓輪播圖也同步顯示新上傳的圖片（因為 galleryImages 目前是寫死的 computed，
//   // 這裡需要把它改成可變動的 ref，詳見下方調整）
//   galleryImages.value = editForm.images;
// }



const commentMode = computed(() => {
  if (context === 'myPosts') return 'seller';
  if (context === 'myApplications') return 'applicant';
  return 'browse';
});


// 該文章留言列表
const articleComments = computed(() =>
  fakeComments.filter(comment => comment.post_id === articleId)
);

const sortOrder = ref('newest');
const sortedComments = computed(() => {
  const list = [...articleComments.value];
  list.sort((a, b) => {

    // 被選中留言至頂
    if (a.is_choose !== b.is_choose) {
      return a.is_choose ? -1 : 1;
    }

    const diff = new Date(a.create_time) - new Date(b.create_time);
    return sortOrder.value === 'newest' ? -diff : diff;
  });
  return list;
});


const activeImageIndex = ref(0);

// 是否為自己刊登的文章
const isOwner = computed(() => {
  if (!article.value) return false;
  return article.value.mem_id === currentUserId;
});


// 留言表單燈箱
const isModalOpen = ref(false);
const form = reactive({
  name: '',
  changeItem: '',
  phone: '',
});
const errors = reactive({
  name: false,
  changeItem: false,
  phone: false
});

function closeModal() {
  isModalOpen.value = false;
}

function resetForm() {
  form.name = '';
  form.changeItem = '';
  form.phone = '';
  errors.name = false;
  errors.changeItem = false;
  errors.phone = false;
}

function handleReset() {
  const hasContent = form.name || form.changeItem || form.phone;
  if (!hasContent) {
    window.alert('請先填入資料');
    return;
  }
  const isConfirm = window.confirm('是否全部清空?');
  if (isConfirm) {
    resetForm();
  }
}

function validateForm() {
  errors.name = form.name.trim() === '';
  errors.changeItem = form.changeItem.trim() === '';
  errors.phone = form.phone.trim() === '';
  return !(errors.name || errors.changeItem || errors.phone);
}

function handleSubmit() {
  if (!validateForm()) {
    window.alert('星號*為必填項目');
    return;
  }

  console.log('送出交換提議：', { ...form });
  // 之後這裡打 API，新增一筆 fakeComments（申請），articleId 用 article.value.id

  closeModal();
  resetForm();
  window.alert('交換申請已成功送出！');
}

// 賣家選擇交換對象
function handleSelectApplicant({ commentId }) {
  const isConfirm = window.confirm('確定要選擇這位會員進行交換嗎？');
  if (!isConfirm) return;

  article.value.status = 'exchanging';
  

  articleComments.value.forEach(comment => {
    comment.is_choose = comment.comm_id === commentId;
  });

  window.alert('已選擇交換對象，等待對方回覆!');

  // 之後接後端：
  // await axios.patch(`/api/exchange/${article.value.post_id}/select`, { commentId });
}


</script>

<style lang="scss" scoped>
@use '@/assets/scss/_var' as *;


p{
  color: #141C26;
}
.owner-actions{
  display: flex;
  gap: 12px;
}

.prod-info-header{
  align-items: center;
  padding-top: 12px;
  gap: 20px;
  flex-direction: row;

  

  .title {
      font-size: map-get($fontSize, h4);
      font-weight: 900;
      // padding-top: 12px;
    }
  .btn-edit{
    color: map-get($color, neutral );
    padding: 4px;
    position: relative;

    &::after{
      content: '';
      width: 0%;
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      height: 1px;
      background-color: map-get($color, neutral );
      transition:all .3s;
    }

    &:hover::after{
      width: 100%;
    }
  }
}

.empty-state{
  text-align: center;
  color: map-get($color, hint );
  padding-block: 8px;
  letter-spacing: 2px;
}




@media (hover: none) {
  .btnFill:hover {
    background: initial;
  }
}



.box-style {
  background-color: white;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid map-get($color, gray);
  box-shadow: 2px 2px 12px rgba(20, 28, 38, 0.1);
  overflow: hidden;
}

.apply-hint {
  color: map-get($color, neutral);
  font-weight: 550;
  text-align: center;
}

// == 留言燈箱 ================================================
.example-modal {
  visibility: hidden;
  opacity: 0;
  pointer-events: none;
  position: fixed;
  inset: 0;
  z-index: 1000;
  transition: opacity .2s ease;

  .modal-mask {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
  }

  &.is-open {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;
  }
}

.empty-msg {
  color: map-get($color, hint);
  padding: 20px 0;
  text-align: center;
}

// == 主體區塊 ================================================


.container {
  display: flex;
  flex-direction: column;
  gap: 40px;

  .prod-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
    flex: 1;

    .tags {
      flex-direction: row;
      .chip--state {
        margin-inline: 8px;
      }
    }

    .user{
      display: flex;
      align-items: center;
      gap: 12px;
      align-items: start;
    }

    .user-info{
      width: 100%;


      .info-header{
        display: flex;
        align-items: center;
        gap: 8px;
        
      }
    }

    .topLine{
      border-top: 1px solid #ddd;
      // padding-top: 8px;
    }
    
    .user-name{
      font-size: 18px;
      font-weight: 550;
      padding-block: 12px;
    }

    

    .prod-txt {
      .title {
        font-size: map-get($fontSize, default);
        font-weight: 600;
      }

      .prod-content {
        line-height: 1.5;
      }
    }

    .btnFill {
      width: fit-content;
    }
  }

  .message-board {
    > h1 {
      font-size: map-get($fontSize, h4);
      padding-bottom: 12px;
    }

    .msg-list {
      ul {
        margin-inline: -16px;
        list-style: none;
      }
    }

    .icon-sort {
      display: flex;
      gap: 12px;
      padding-bottom: 12px;

      button {
        background: none;
        border: none;
        color: map-get($color, hint);
        cursor: pointer;
        font-size: 14px;

        &.--active {
          color: map-get($color, neutral);
          font-weight: 600;
        }
      }
    }
  }

  .panel {
    max-width: 500px;

    .gallery-wrap {
      position: relative;
      margin: 0 auto;

      // vue3-carousel：手機版先隱藏箭頭
      :deep(.carousel__prev),
      :deep(.carousel__next) {
        display: none;
      }

      // 手機版：dots 顯示在主圖下方
      :deep(.carousel__pagination) {
        margin-top: 12px;
      }

      :deep(.carousel__pagination-button) {
        background-color: map-get($color, neutral);
        opacity: .5;
        width: 16px;
        height: 4px;
      }

      :deep(.carousel__pagination-button--active) {
        background-color: map-get($color, primary);
        opacity: 1;
      }

      .img-big {
        border: 1px solid map-get($color, gray);
        overflow: hidden;
        border-radius: 4px;

        img {
          width: 100%;
          display: block;
          aspect-ratio: 1 / 1;
          object-fit: cover;
        }
      }
    }

    // 手機先隱藏縮圖列
    .slider-nav {
      display: none;
    }
  }
}

// == 留言表單燈箱樣式 ================================================
#addMsg {
  padding: 20px;
  position: fixed;
  width: 40%;
  max-width: 400px;
  min-width: 300px;
  background-color: map-get($color, tertiary);
  border-radius: 16px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);

  .btn-close {
    display: flex;
    justify-content: end;

    button {
      background: none;
      border: none;
      color: map-get($color, neutral);
      padding: 4px;
      padding-top: 0;
      cursor: pointer;
    }
  }

  form {
    display: flex;
    flex-direction: column;
    gap: 12px;

    .form-title {
      font-size: map-get($fontSize, default);
      text-align: center;
      font-weight: 600;
    }

    .form-hint {
      font-size: map-get($fontSize, hint);
      color: map-get($color, hint);
      line-height: 1.5;
    }
  }
}

// == 桌機 ================================================
@media screen and (width >= 992px) {
  .next-page {
    padding: 12px 0 4px;

    &::after {
      content: '';
      position: absolute;
      left: 50%;
      bottom: 0;
      width: 0;
      height: 2px;
      background-color: map-get($color, neutral);
      transition: .3s ease;
    }

    &:hover::after {
      width: 100%;
      transform: translateX(-50%);
    }
  }

  .container {
    padding-top: 8px;
    flex-direction: row;

    // 商品圖片
    .panel {
      width: 80%;
      padding-inline: 44px;
      padding-top: 12px;
      position: sticky;
      top: 90px;
      height: fit-content;

      .gallery-wrap {
        overflow: visible;

        // 桌機關閉內建 dots，改用下面的縮圖列
        :deep(.carousel__pagination) {
          display: none;
        }

        // 箭頭
        :deep(.carousel__prev),
        :deep(.carousel__next) {
          display: block;
          background-color: white;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          box-shadow: 2px 2px 8px rgba(20, 28, 38, 0.15);
          transition: all .3s;

          svg {
            fill: map-get($color, primary);
          }

          &:hover {
            background-color: map-get($color, primary);

            svg {
              fill: white;
            }
          }
        }

        :deep(.carousel__prev) {
          left: -44px;
        }

        :deep(.carousel__next) {
          right: -44px;
        }
      }

      .slider-nav {
        display: flex;
        gap: 12px;
        margin-top: 14px;

        .thumb {
          flex: 1 1 0;
          aspect-ratio: 1 / 1;
          border-radius: 4px;
          border: 1px solid map-get($color, gray);
          overflow: hidden;
          cursor: pointer;
          transition: border-color .15s ease;
          max-width: 74px;

          img {
            width: 100%;
            height: 100%;
            object-fit: cover;
          }

          &.--active {
            border: 1.5px solid map-get($color, primary);
          }
        }
      }
    }

  }

  #addMsg {
    form {
      gap: 20px;

      .form-title {
        font-size: map-get($fontSize, h4);
        font-weight: 700;
      }

      .form-btn {
        .btnNoFill:hover {
          background-color: map-get($color, neutral);
          border-color: map-get($color, neutral);
          color: map-get($color, tertiary);
        }
      }
    }
  }
}
</style>