<template>
  <div class="author-card">
    <img src="/spinix_member_test2.jpg" alt="writername" class="img-writer">
    <p class="name-writer">Blader_X</p>
    <p class="score-writer">勝場數：100</p>
    <!-- <img :src="writer.img" :alt="writer.name" class="img-writer">
    <p class="name-writer">{{ writer.name }}</p>
    <p class="score-writer">{{ writer.score }}</p> -->
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
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.author-card {
  background-color: white;
  display: flex;
  
  
  @include rwd("desktop"){
    box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    width: 200px;
    height: 240px;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .img-writer{
    width: 10%;
    // height: auto;
    aspect-ratio: 1/1 ;
    border-radius: 50%;

    @include rwd("desktop") {
      width: 50%;
    }
  }
}

</style>