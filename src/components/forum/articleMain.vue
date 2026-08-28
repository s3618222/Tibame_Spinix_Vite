<template>
  <main class="article-main">
    <aside class="author-card">
      <AuthorCard :writer="articleAuthor" />
    </aside>
    
    <article class="article-content">
      <header class="header-article">
        <div class="title-label">
          <p class="type-article chip">{{ article.category }}</p>
          <div class="time-post">
            <i class="fa-regular fa-clock"></i>
            <span>{{ article.createTime }}</span>
          </div>
        </div>
        <h1>{{ article.title }}</h1>
      </header>
      <section class="body-article tinymce-content" v-html="article.content"></section>
      <footer class="footer-article" v-if="article.isShow !== false">
        <button type="button" @click="handleShare">
          <i class="fa-solid fa-share-nodes"></i>
          <span>分享</span>
        </button>
        <a v-if="!isAuthor" :href="`${baseURL}complaint.html`" type="button">
          <i class="fa-regular fa-flag"></i>
          <span>檢舉</span>
        </a>
      </footer>
    </article>
  </main>
  
  

</template>

<script>
import AuthorCard from '@/components/forum/authorCard.vue';

  export default {
    name: "ArticleMain",
    
    components: {
      AuthorCard
    },

    props: {
      articleAuthor: {
        type: Object,
        default: () => ({})
      },
      article: {
        type: Object,
        default: () => ({})
      },
      isAuthor: {
        type: Boolean,
        default: false
      }
    },

    data(){
      return{
        baseURL: import.meta.env.BASE_URL
      }
    },

    methods: {
      async handleShare() {
        try {
          await navigator.clipboard.writeText(window.location.href);
          alert("已複製貼文連結！");
        } catch (error) {
          console.error("複製連結失敗", error);
        }
      }
    }
  }
  

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;
@use '@/assets/scss/component/tinymceContent' as *;

.article-main{
  padding: 16px;
  display: flex;
  flex-direction: column;
  // padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);
  gap: 16px;
  
  @include rwd("desktop"){
    // margin-top: 24px;
    padding: 0;
    flex-direction: row;
    gap: 8px;
    
    background-color: transparent;
    border-radius: 0;
    box-shadow: none;
  }

  i {
    // color: red;
    margin-right: 4px;
  }
}

.article-content {
  width: 100%;
  display: contents;  // 消失外殼容器的特性 
  // padding: 20px;

  @include rwd("desktop"){
    display: flex;
    flex-direction: column;
    padding: 24px;
    background-color: #fff;
    border-radius: 8px;
    gap: 24px;
    box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);
  }
}

.header-article {
  order: 1;
  padding-bottom: 8px;

  @include rwd("desktop") {
    order: initial;

    padding-bottom: 16px;
    border-bottom: 1px solid map-get($color, tertiary );
  }

  .title-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    // margin-bottom: 8px;
    padding-block: 12px;
    font-size: 14px;

    .type-article {
      background-color: map-get($color , primary );
    }

    .time-post {
      color: map-get($color , neutral );
    }
  }

  h1 {
    font-size: map-get($fontSize , h1 );
    color: map-get($color, secondary);
  }
}

.author-card {
  order: 2;

  @include rwd("desktop") {
    order: initial;
    position: sticky;
    top: 100px;
  }
}

.body-article {
  order: 3;

  @include rwd("desktop") {
    order: initial;
  }

  
}

.footer-article {
  order: 4;
  display: flex;
  justify-content: space-between;
  
  button, a {
    color: map-get($color , neutral );
    cursor: pointer;
    font-size: 16px;
    font-weight: normal;
    line-height: 16px;

    &:hover {
      color: lighten(map-get($color , neutral ), 10%);
    }
  }

  @include rwd("desktop") {
    order: initial;

    padding-top: 16px;
    border-top: 1px solid map-get($color, tertiary );
  }
}
</style>