<template>
  <div class="author-card">
    <img :src="avatarUrl" :alt="writer.name" class="img-writer">
    <div class="txt-box">
      <p class="name-writer">{{ writer.name }}</p>
      <p class="score-writer chip chip--state">{{ writer.score }}</p>
    </div>
  </div>
  
</template>

<script>
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";
export default {
  name: "AuthorCard",

  props: {
    writer: {
      type: Object,
      default: () => ({})
    }
  },

  computed: {
    avatarUrl() {
      const baseUrl = import.meta.env.BASE_URL;
      if (!this.writer.img) {
        return `${baseUrl}spinix_member_default.png`;
      }

      if (this.writer.img.startsWith("uploads/member/")) {
        return `${phpBaseUrl}/${this.writer.img}`;
      }

      return `${baseUrl}${this.writer.img}`;
    }
  }
}

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.author-card {
  width: 100%;
  background-color: white;
  display: flex;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid map-get($color, tertiary );
  
  .img-writer{
    display: block;
    width: 50px;
    // height: auto;
    aspect-ratio: 1/1 ;
    border-radius: 50%;
  }

  .txt-box {
    display: flex;
    align-items: center; gap: 8px;
    width: 100%;
    min-width: 0;

    .name-writer {
      min-width: 0;
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 16px;
      min-height: 18px;
      max-width: 80%;
    }

    .score-writer {
      flex-shrink: 0;
    }
  }
}

@include rwd("desktop"){
  .author-card {
    box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    width: 200px;
    height: 240px;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    padding: 0;
    border-bottom: 0px;

    .img-writer {
      width: 50%;
      // max-width: 50%;
    }

    .txt-box {
      flex-direction: column;
    }
  }

  
}
</style>