<template>
  <div class="forum-article-page">
    <a :href="`${baseUrl}forum.html`" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      <span>返回論壇列表</span>
    </a>
    <div class="article-content">
      <ArticleMain :article-author="articleAuthor"/>
      <CommentList :comments="comments"/>
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
      baseUrl: import.meta.env.BASE_URL,
      articleAuthor: {
        name: "Blader_X",
        score: "勝場數：100",
        img: ""
      },
      comments: [
        {
          id: 1,
          floor: 2,
          time: "2026-05-12 15:38",
          content: "X軸心確實很猛，但如果遇到防禦特化的對手，開局沒撞飛就幾乎等於輸了。我個人更傾向用 J (Jolt) 軸，稍微保留一點可控性，容錯率比較高。",
          commenter: { name: "打野戰神", score: "勝場數：76", img: "" }
        },
        {
          id: 2,
          floor: 3,
          time: "2026-05-12 16:05",
          content: "同意樓上，不過如果對手也是攻擊型，X軸心開局互撞反而很吃機運，我自己實測過幾場都是先手優勢比較重要。",
          commenter: { name: "陀螺新手阿翔", score: "勝場數：12", img: "" }
        },
        {
          id: 3,
          floor: 4,
          time: "2026-05-12 17:20",
          content: "請問這套配裝在防禦組合上有推薦嗎？想搭配鐵盤試試看穩定度。",
          commenter: { name: "配裝控", score: "勝場數：203", img: "" }
        }
      ]
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