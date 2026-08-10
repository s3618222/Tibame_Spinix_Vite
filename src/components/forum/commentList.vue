<template>
  <CommentItem/>
  
</template>

<script>
import CommentItem from '@/components/forum/commentItem.vue';


  export default {
    name: "CommentList",

    components: {
      CommentItem
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;


</style>