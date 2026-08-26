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
          <span class="num">{{ articles.length }}</span>
          <span class="unit">則貼文</span>
        </div>
      </div>
      <div class="total-item">
        <p>總計留言數</p>
        <div class="amount-total">
          <span class="num">{{ comments.length }}</span>
          <span class="unit">則留言</span>
        </div>
      </div>
    </div>
    <div class="control-tab">
      <button type="button"
          :class="['tab-btn', { active: currentTab === 'posts' }]"
          @click="switchTab('posts')">
          我的貼文
        </button>
        <button type="button"
        :class="['tab-btn', { active: currentTab === 'comments' }]"
        @click="switchTab('comments')">
          我的留言
        </button>
    </div>
    <div class="table-content">
      <MyArticles v-if="currentTab === 'posts'" :articles="articles" @delete-article="handleDeleteArticle"/>
      <myComments v-if="currentTab === 'comments'" :comments="comments" @delete-message="handleDeleteMessage"/>
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
      articles: [],
      comments: []
    };
  },

  created(){
    this.fetchMyArticles();
    this.fetchMyComments();
  },

  methods: {
    switchTab(tab) {
      this.currentTab = tab;
    },

    async fetchMyArticles() {
      try {
        const res = await fetch("http://localhost:8888/Spinix/php/forum/getMyArticles.php", {
          method: "GET",
          credentials: "include"
        });
        const result = await res.json();

        if (result.success) {
          this.articles = result.data.map(article => ({
            id: article.art_id,
            title: article.title,
            category: article.category,
            date: article.create_time.split(" ")[0],
            commentCount: article.comment_count,
            isShow: Number(article.is_show) === 1,
            removeReason: article.remove_reason
          }));
        }
      } catch (error) {
        console.error("我的文章列表載入失敗", error);
      }
    },

    async fetchMyComments() {
      try {
        const res = await fetch("http://localhost:8888/Spinix/php/forum/getMyComments.php", {
          method: "GET",
          credentials: "include"
        });
        const result = await res.json();

        if (result.success) {
          this.comments = result.data.map(comment => ({
            id: comment.msg_id,
            articleId: comment.art_id,
            articleTitle: comment.title,
            content: comment.content,
            date: comment.create_time.split(" ")[0],
            isShow: Number(comment.is_show) === 1
          }));
        }
      } catch (error) {
        console.error("我的留言列表載入失敗", error);
      }
    },

    async handleDeleteArticle(articleId){
      try {
        const formData = new FormData();
        formData.append("art_id", articleId);

        const res = await fetch("http://localhost:8888/Spinix/php/forum/deleteArticle.php", {
          method: "POST",
          credentials: "include",
          body: formData
        });
        const result = await res.json();

        if (result.success) {
          this.articles = this.articles.filter(article => article.id !== articleId);
          alert("刪除成功");
        } else {
          alert(result.message || "刪除失敗，請稍後再試");
        }
      } catch (error) {
        console.error("刪除文章失敗", error);
      }
    },

    async handleDeleteMessage(messageId){
      try {
        const formData = new FormData();
        formData.append("msg_id", messageId);

        const res = await fetch("http://localhost:8888/Spinix/php/forum/deleteComment.php", {
          method: "POST",
          credentials: "include",
          body: formData
        });
        const result = await res.json();

        if (result.success) {
          this.comments = this.comments.filter(comment => comment.id !== messageId);
          alert("刪除成功");
        } else {
          alert(result.message || "刪除失敗，請稍後再試");
        }
      } catch (error) {
        console.error("刪除留言失敗", error);
      }
    }
  }
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