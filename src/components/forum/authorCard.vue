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
        return this.writer.img
          ? `${baseUrl}${this.writer.img}`
          : `${baseUrl}spinix_member_default.png`;
      }
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.author-card {
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