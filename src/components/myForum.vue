<template>
  <section class="my-forum">
    <div class="title-forum">
      <h2>我的論壇</h2>
      <p>記錄過去您所發布的貼文以及留言。</p>
    </div>
    <div class="count-title">
      <div class="total-item">
        <p>總計貼文數</p>
        <div class="amount-total">
          <span class="num">25</span>
          <span class="unit">則貼文</span>
        </div>
      </div>
      <div class="total-item">
        <p>總計留言數</p>
        <div class="amount-total">
          <span class="num">211</span>
          <span class="unit">則留言</span>
        </div>
      </div>
    </div>
    <div class="control-tab">
      <button type="button" 
          class="tab-btn active"
          @click="changeRole('initiator')">
          我的貼文
        </button>
        <button type="button"
        class="tab-btn"
        @click="changeRole('participant')">
          我的留言
        </button>
        <!-- :class="['tab-btn', { active: currentTab == 'comments' }]" -->
    </div>
    <div class="table-content">
      <MyArticles/>
      <!-- <myComments/> -->

    </div>
  </section>
</template>

<script>
import myComments from "@/components/member/forum/myComments.vue";
import MyArticles from "@/components//member/forum/myArticles.vue";
export default {
  name: "MyForum",

  components: {
    myComments,
    MyArticles
  },

  data() {
    return {
      currentTab: "posts",
      myPosts: [
        { id: 1, category: "配裝開箱", title: "WX-01 爆裂天龍分析", date: "2026-05-12", commentCount: 34 },
        { id: 2, category: "新手發問", title: "新手求推薦平民配置", date: "2026-05-10", commentCount: 12 }
      ],
      myComments: [
        { id: 101, articleTitle: "WX-01 爆裂天龍分析", content: "這套配置真的很強！", date: "2026-05-12" }
      ]
    };
  },
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.my-forum {
  width: 100%;
  min-width: 0;
  padding: 4px 0 36px;

  display: flex;
  flex-direction: column;
  gap:32px;
}

h2 {
  color: map-get($color , secondary2 );
  font-size: map-get($fontSize, h2 );
  font-weight: 700;
  margin-bottom: 12px;
}

p {
  color: map-get($color, secondary );
  font-size: 18px;
}

.count-title {
  display: flex;
  gap: 16px;
}
.total-item {
  border-radius: 12px;
  padding: 24px;
  background-color: #fff;
  width: 100%;
  border: 1px solid map-get($color, warmGray );
  font-size: 14px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);

  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;

  p {
    color: map-get($color, black );
    font-weight: normal;
    font-size: 14px;

    @include rwd("tablet") {
      font-size: 18px;
      align-items: flex-start;
    }
  }

  @include rwd("tablet") {
    align-items: flex-start;
  }
}
.num {
  font-size: 30px;
  color: map-get($color, secondary2 );
  font-weight: 800;

  @include rwd("tablet") {
    font-size: 48px;
  }
}
.unit {
  display: none;

  @include rwd("tablet") {
    display: inline;
    font-size: 14px;
    color: map-get($color, neutral );
    margin-left: 4px;
  }
}

.tab-btn {
  color: rgba(20, 28, 38, 0.5);
  font-size: 16px;
  line-height: 1.5;

  background-color: transparent;
  padding: 0 12px;
  border: 0;
  border-left: 3px solid rgba(20, 28, 38, 0.5);
  cursor: pointer;

  // 點擊切換時的樣式
  &.active {
  border-left-color: #f29b00;
  color: #141c26;
  }

  
}
</style>