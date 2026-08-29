<template>
  <div class="forum-toolbar">
    <div class="sort-dropdown">
      <select :value="sortBy" @change="$emit('sort', $event.target.value)">
        <option value="latestPost">依最新發文時間</option>
        <option value="latestComment">依最新留言時間</option>
      </select>
    </div>
    <div class="search-box">
      <!-- 放大鏡 -->
      <i class="fa-solid fa-magnifying-glass"></i>
      <input
        type="text"
        placeholder="搜尋討論串..."
        :value="searchKeyword"
        @input="$emit('search', $event.target.value)"
      >
    </div>
    <a :href="`${baseUrl}forumForm.html`" @click.prevent="handlePostClick" type="button" class="btnFill btn-post">
      <i class="fa-regular fa-pen-to-square"></i>
      <span>我要發文</span>
    </a>



  </div>
</template>

<script>
  import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl";

  export default {
    name: "ForumToolbar",

    props: {
      searchKeyword: {
        type: String,
        default: ""
      },
      sortBy: {
        type: String,
        default: "latestPost"
      }
    },

    emits: ["search", "sort"],

    data(){
      return {
        baseUrl: import.meta.env.BASE_URL
      }
    },

    methods: {
      async handlePostClick() {
        try {
          const res = await fetch(`${phpBaseUrl}/member/currentMember_get.php`, {
            credentials: "include"
          });
          const result = await res.json();

          if (result.isLoggedIn) {
            window.location.href = `${this.baseUrl}forumForm.html`;
          } else {
            alert("請先登入才能發表文章");
            window.location.href = `${this.baseUrl}signIn.html`;
          }
        } catch (error) {
          console.error("登入狀態確認失敗", error);
        }
      }
    }
  }
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/component/button' as *;


.forum-toolbar {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;

  @media (min-width: 768px) {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.sort-dropdown {
  order: 2;
  background-color: white;
  padding: 8px 12px;
  border-radius: 8px;
  width: fit-content;
  align-self: flex-end;

  @media (min-width: 768px) {
    // display: inline-flex;
  }
  
  select {
    order: 1;
    width: 160px;
    // appearance: none;
    // -webkit-appearance: none;
    // -moz-appearance: none;
    outline: none;
    border: none;
    padding: 0;
    margin: 0;
    background-color: transparent;
  }
  
}

.search-box{
  order: 1;
  background-color: #fff;
  padding: 8px 12px;
  border-radius: 8px;
  display: flex;
  gap: 4px;
  align-items: center;

  @media (min-width: 768px) {
    order: 2;
    width: fit-content;
  }

  input {
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    outline: none;
    border: none;
    padding: 0;
    margin: 0;
    border-radius: 0;
    box-shadow: none;
    background-color: transparent;
  }
}

.btn-post {
  // --------------------------------------------------
  // 1. 手機版預設 (< 768px)：固定在右下角的圓形 FAB 按鈕
  // --------------------------------------------------
  
  position: fixed;
  bottom: 24px;
  right: 20px;
  z-index: 99;

  // 覆寫同學的尺寸與圓角
  width: 56px;
  height: 56px;
  padding: 0;           /* 清除同學原本的 padding: 8px 24px */
  border-radius: 50%;   /* 將同學的 12px 圓角覆寫成正圓形 */

  // 用 Flexbox 讓裡面的 Icon 精准置中
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);

  // 關鍵：手機版隱藏文字，只留 Icon
  span {
    display: none;
  }

  // --------------------------------------------------
  // 2. 電腦版 (>= 768px)：還原成同學原始的長方形按鈕樣式
  // --------------------------------------------------
  @media (min-width: 768px) {
    order: 3;
    position: static;    /* 取消 fixed 右下角定位，回到文章列表最上方 */
    width: auto;
    height: auto;
    padding: 8px 24px;   /* 還原同學寫的內距 */
    border-radius: 12px; /* 還原同學寫的 12px 圓角 */
    box-shadow: none;

    // 顯示文字，並與左邊 icon 保持間距
    span {
      display: inline;
      margin-left: 6px;
    }
  }
}



</style>