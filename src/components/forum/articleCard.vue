<template>
  <a :href="`${baseUrl}forumArticle.html?id=${article.id}`" class="article-card-link">
    <article class="article-card">
      <p class="type-article">{{article.type}}</p>
      
      <div class="main-content">
        <div class="text-group">
          <div class="label-article"></div><!-- 如果有空做置頂功能的話 -->
          <p class="title-article">{{ article.title }}</p>
          <p class="content-preview">{{ contentPreview }}</p>
        </div>
        <!-- <img :src="imgURL" :alt="article.title" class="img-article"> -->
        
      </div>

      <div class="post-info">
        <img :src="avatarUrl" :alt="article.name" class="img-writer">
        <div class="post-txt">
          <p class="name-post">{{ article.name }}</p>
          <p class="time-post">{{ relativeTime }}</p>
        </div>
        
      </div>
      
      <div class="comment-num">
        <i class="fa-regular fa-message"></i>
        <span>{{ article.comment }}</span>
      </div>
    </article>
  </a>
  

</template>

<script>
import { getRelativeTime } from "@/assets/js/utils/formatRelativeTime.js";
import { stripHtmlTags } from "@/assets/js/utils/stripHtmlTags.js";
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";

export default {
  name: "ArticleCard",

  props: ["article"],

  data(){
    return {
      baseUrl: import.meta.env.BASE_URL
    }
  },

  computed: {
    imgURL() {
      return `${baseUrl}${this.article.image.replace(/^\//, '')}`;
    },
    avatarUrl() {
      if (!this.article.imgWriter) {
        return `${this.baseUrl}spinix_member_default.png`;
      }

      if (this.article.imgWriter.startsWith("uploads/member/")) {
        return `${phpBaseUrl}/${this.article.imgWriter}`;
      }

      return `${this.baseUrl}${this.article.imgWriter}`;
    },
    relativeTime() {
      return getRelativeTime(this.article.createTime);
    },
    contentPreview() {
      return stripHtmlTags(this.article.content);
    }
  }
}
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.article-card-link {
  text-decoration: none;
  display: block;
}

.article-card {
  background-color: map-get($color, white);
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 12px;

  // 手機版預設：Grid 佈局
  display: grid;
  grid-template-columns: 1fr auto;
  grid-template-areas:
    "class class"
    "main  main"
    "info  comment";
  gap: 12px;
  align-items: center;

  // 電腦版微調 (>= 992px)

  @include rwd("desktop") {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    border-radius: 0;
    border-bottom: 1px solid map-get($color, gray);
    margin-bottom: 0;
    padding: 20px 24px;
    gap: 20px;

    &:hover {
      background-color: map-get($color , lightYellow );
    }
  }
}



  // 1. 分類標籤

.type-article {
  grid-area: class;
  color: map-get($color, secondary2);
  font-weight: bold;
  font-size: map-get($fontSize, hint);
  
  @include rwd("desktop") {
    text-align: center;
    min-width: 84px;
    width: fit-content;
    flex-shrink: 0;
  }
}

  // 2. 文章主要內容區
.main-content {
  grid-area: main;
  display: flex;
  flex-direction: column;
  gap: 4px;

  @include rwd("desktop") {
    flex: 1;
    flex-direction: row; 
    align-items: center; 
    gap: 16px;
    min-width: 0;
  }
}


.img-article {
  order: 2;
  width: 50%;
  aspect-ratio: 16 / 9;
  object-fit: cover;
  object-position: center;
  border-radius: 8px;

  &[src=""], &:not([src]) {
    display: none;
  }

  @include rwd("desktop") {
    order: 1; 
    width: 60px;
    height: 60px;
    aspect-ratio: auto;
    flex-shrink: 0;
    border-radius: 6px;
  }
}

// 文字區塊（包含標題與內文）
.text-group { 
  order: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 100%;
  min-width: 0;
  
  @include rwd("desktop") {
    order: 2; 
    flex: 1;
    // max-width: 500px;
  }

  .title-article {
    width: 100%;
    font-size: map-get($fontSize, default); 
    font-weight: bold;
    color: map-get($color, black); 
    line-height: 1.4;

    white-space: nowrap;    /* 1. 強制不換行 */
    overflow: hidden;       /* 2. 裁切超出容器範圍的文字 */
    text-overflow: ellipsis;
  }

  .content-preview {
    font-size: map-get($fontSize, hint); 
    color: map-get($color, neutral); 
    line-height: 1.5;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}



  // 3. 發文者資訊
.post-info {
  grid-area: info;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: map-get($color, neutral);
  
  @include rwd("desktop") {
    justify-content: flex-start;
    width: 160px;
    flex-shrink: 0;
    // justify-content: flex-end;
  }

  .img-writer {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;

    @include rwd("desktop") {
      width: 40px;
      height: 40px;
    }
  }

  .post-txt {
    display: flex;
    flex-direction: row;
    gap: 8px;
    width: 100%;
    min-width: 0;

    @include rwd("desktop") {
      flex-direction: column;
    }

    .name-post {
      max-width: 108px;
      color: map-get($color , secondary );
      font-weight: 500;
      white-space: nowrap;    /* 1. 強制不換行 */
      overflow: hidden;       /* 2. 裁切超出容器範圍的文字 */
      text-overflow: ellipsis;
    }
  }
  
}

  // 4. 留言數
.comment-num {
  grid-area: comment;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: map-get($color, neutral);

  @include rwd("desktop") {
    width: 80px;
    flex-shrink: 0;
    justify-content: flex-end;
  }
}
</style>