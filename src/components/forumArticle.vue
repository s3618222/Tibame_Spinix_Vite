<template>
  <div class="forum-article-page">
    <a :href="`${baseUrl}forum.html`" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      <span>返回論壇列表</span>
    </a>
    <div class="not-found" v-if="notFound">
      <p>找不到這篇文章，可能已被移除或您沒有權限查看。</p>
    </div>
    <div class="article-content" v-else>
      <WarningBanner
        v-if="article.isShow === false"
        title="文章"
        :remove_reason="article.removeReason"
        :show_contact="true"
      />
      <ArticleMain :article-author="articleAuthor" :article="article" :is-author="isArticleAuthor"/>
      <CommentList :comments="comments" :is-article-show="article.isShow !== false" :current-member-id="currentMemberId"/>
      <CommentForm v-if="article.isShow !== false" @submit-comment="handleSubmitComment" :is-logged-in="currentMemberId !== null"/>
    </div>

  </div>
  
</template>

<script>
import ArticleMain from '@/components/forum/articleMain.vue';
import CommentForm from '@/components/forum/commentForm.vue';
import CommentList from '@/components/forum/commentList.vue';
import WarningBanner from '@/components/WarningBanner.vue';
import { CATEGORY_LABELS } from '@/assets/js/utils/articleCategory.js';
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl";


export default {
  name: "ForumArticle",

  components: {
    ArticleMain,
    CommentForm,
    CommentList,
    WarningBanner
  },

  data(){
    return {
      baseUrl: import.meta.env.BASE_URL,
      articleId: null,
      articleAuthor: {},
      comments: [],
      article: {},
      notFound: false,
      currentMemberId: null
    }
  },

  computed: {
    isArticleAuthor(){
      return this.currentMemberId !== null && Number(this.currentMemberId) === Number(this.article.memId);
    }
  },

  created(){
    const params = new URLSearchParams(window.location.search);
    this.articleId = params.get('id');   // 在這裡解析當前文章id，存起來
    
    this.fetchArticle();
    this.fetchComments();
    this.fetchCurrentMember();
  },

  methods: {
    async fetchArticle(){
      try{
        const res = await fetch(`${phpBaseUrl}/forum/getArticleById.php?id=${this.articleId}`, {credentials: "include"});
        const result = await res.json();

        if(result.success){
          const articleData = result.data;
          this.articleAuthor = {
            name: articleData.author_name,
            score: `勝場數：${articleData.author_battle_wins}`,
            img: articleData.author_photo
          };
          this.article = {
            title: articleData.title,
            content: articleData.content,
            category: CATEGORY_LABELS[articleData.category] ?? articleData.category,
            createTime: articleData.create_time,
            isShow: Number(articleData.is_show) === 1,
            removeReason: articleData.remove_reason,
            memId: articleData.mem_id
          }

          // 修改網頁title
          document.title = `${this.article.title} | Spinix論壇`;
        } else {
          this.notFound = true;
          document.title = `找不到您要的文章 | Spinix論壇`;
        }
      }catch(error){
        console.error("文章資料載入失敗", error);
      }
    },

    async fetchComments(){
      try{
        const res = await fetch(`${phpBaseUrl}/forum/getComments.php?id=${this.articleId}`, {credentials: "include"});
        const result = await res.json();
        if (result.success) {
          this.comments = result.data.map((c, index) => ({
            id: c.msg_id,
            floor: index + 2,
            content: c.content,
            time: c.create_time,
            pic: c.pic,
            memId: c.mem_id,
            isShow: Number(c.is_show) === 1,  // 所有上架中留言
            commenter: {
              name: c.commenter_name,
              score: `勝場數：${c.commenter_battle_wins}`,
              img: c.commenter_photo
            }
          }));
        }
      }catch(error){
        console.error("留言資料載入失敗", error);
      }
    },

    async fetchCurrentMember(){
      try{
        const res = await fetch(`${phpBaseUrl}/member/currentMember_get.php`, {credentials: "include"});
        const result = await res.json();

        if (result.isLoggedIn) {
          this.currentMemberId = result.member.id;
        }

      }catch(error){
        console.error("登入狀態確認失敗", error);
      }
    },

    async handleSubmitComment(payload){
      // payload 參數就是留言表單送過來的物件
      try{
        const formData = new FormData();
        formData.append("art_id", this.articleId);
        formData.append("content", payload.content);   

        if (payload.image) {
          formData.append("image", payload.image);
        }

        const res = await fetch(`${phpBaseUrl}/forum/addComment.php`, {
          method: "POST",
          credentials: "include",
          body: formData
        });
        const result = await res.json();

        if(result.success){
          const newComment = {
            id: result.data.msg_id,
            floor: this.comments.length + 2,
            content: result.data.content,
            time: result.data.create_time,
            pic: result.data.pic,
            memId: result.data.mem_id,
            isShow: true,
            commenter: {
              name: result.data.commenter_name,
              score: `勝場數：${result.data.commenter_battle_wins}`,
              img: result.data.commenter_photo
            }
          };
          this.comments.push(newComment);
          alert("留言成功！");
        }else{
          alert(result.message || "留言失敗，請稍後再試");
        }
      }catch(error){
        console.error("送出留言失敗", error);
      }
    }
  }
}

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;
// @use '@/assets/scss/reset' as *;


.forum-article-page{
  min-height: 100vh;
  padding: 32px 20px 100px;
  background-color: map-get($color, tertiary);
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #666;
  text-decoration: none;
  font-size: 14px;
  margin-bottom: 16px;

  &:hover { color: #333; }
}

.article-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.not-found {
  text-align: center;
  padding: 80px 20px;
  color: map-get($color, secondary);

  p {
    margin-bottom: 16px;
    font-size: 16px;
  }
}

</style>