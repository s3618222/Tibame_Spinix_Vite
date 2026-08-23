<template>
  <CommentItem
    v-for="item in comments"
    :key="item.id"
    :comment="item"
    :is-article-show="isArticleShow"
    :current-member-id="currentMemberId"
  />

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
      },
      isArticleShow: {
        type: Boolean,
        default: true
      },
      currentMemberId: {
        type: [Number, String],  // 回傳的可能是數值或字串(null)，可以同時宣告多個型別
        default: null
      }
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;


</style>