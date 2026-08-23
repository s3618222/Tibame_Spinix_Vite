<template>
  <div class="article-list">
    <!-- 卡片區 -->
    <ArticleCard v-for="item in articleList" :key="item.id" :article="item"></ArticleCard>

    <!-- 空狀態 -->
    <p v-if="articleList.length === 0" class="empty-state">這個分類目前還沒有文章喔！</p>

    <!-- 分頁器 -->
  </div>

</template>

<script>
  import ArticleCard from './articleCard.vue';

  export default {
    name: "ArticleList",

    components: {ArticleCard},

    props: {
      articleList: {
        type: Array,
        default: () => []
      }
    }
  }
</script>

<style lang="scss" scoped>
@use "sass:map";
@use '@/assets/scss/var' as *;

  .article-list {
    display: flex;
    flex-direction: column;
  }

  .empty-state {
    text-align: center;
    color: map.get($color, neutral);
    padding: 40px 0;
  }
</style>