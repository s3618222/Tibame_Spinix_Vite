<template>
  <div class="forum-article-page">
    <a :href="`${baseUrl}forum.html`" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      <span>返回論壇列表</span>
    </a>
    <div class="article-content">
      <ArticleMain :article-author="articleAuthor" :article="article"/>
      <CommentList :comments="comments"/>
      <CommentForm/>
    </div>
    
  </div>
  
</template>

<script>
import ArticleMain from '@/components/forum/articleMain.vue';
import CommentForm from '@/components/forum/commentForm.vue';
import CommentList from '@/components/forum/commentList.vue';
import { CATEGORY_LABELS } from '@/assets/js/utils/articleCategory.js';


export default {
  name: "ForumArticle",

  components: {
    ArticleMain,
    CommentForm,
    CommentList
  },

  data(){
    return {
      baseUrl: import.meta.env.BASE_URL,
      articleAuthor: {},
      comments: [],
      article: {}
    }
  },
  created(){
    this.fetchArticle();
    this.fetchComments();
  },
  methods: {
    async fetchArticle(){
      const params = new URLSearchParams(window.location.search);
      const articleId = params.get('id');
      
      try{
        const res = await fetch(`http://localhost:8888/Spinix/php/forum/getArticleById.php?id=${articleId}`);
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
            createTime: articleData.create_time
          }
        }
      }catch(error){
        console.error("文章資料載入失敗", error);
      }
    },

    async fetchComments(){
      const params = new URLSearchParams(window.location.search);
      const articleId = params.get('id');

      try{
        const res = await fetch(`http://localhost:8888/Spinix/php/forum/getComments.php?id=${articleId}`);
        const result = await res.json();
        if (result.success) {
          this.comments = result.data.map((c, index) => ({
            id: c.msg_id,
            floor: index + 2,
            content: c.content,
            time: c.create_time,
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

</style>