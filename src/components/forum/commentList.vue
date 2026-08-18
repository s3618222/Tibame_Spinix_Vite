<template>
  <CommentItem v-for="item in comments" :key="item.id" :comment="item"/>

</template>

<script>
import CommentItem from '@/components/forum/commentItem.vue';


  export default {
    name: "CommentList",

    components: {
      CommentItem
    },

    props: {
      comments: {
        type: Array,
        default: () => []
      }
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;


</style>