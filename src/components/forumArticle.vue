<template>
  <div class="forum-article-page">
    <a :href="`${baseUrl}forum.html`" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      <span>返回論壇列表</span>
    </a>
    <div class="article-content">
      <ArticleMain/>
      <CommentList/>
      <CommentForm/>
    </div>
    
  </div>
  
</template>

<script>
import ArticleMain from '@/components/forum/articleMain.vue';
import CommentForm from '@/components/forum/commentForm.vue';
import CommentList from '@/components/forum/commentList.vue';



export default {
  name: "ForumArticle",

  components: {
    ArticleMain,
    CommentForm,
    CommentList
  },

  data(){
    return {
      baseUrl: import.meta.env.BASE_URL
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