<template>
  <section class="comment-item">
    <aside class="author-card">
      <AuthorCard :writer="comment.commenter" />
    </aside>

    <article class="comment-content">
      <div class="comment-header">
        <div class="floor-num chip">樓層：{{ comment.floor }}</div>
        <div class="time-post">
          <i class="fa-regular fa-clock"></i>
          <span>{{ comment.time }}</span>
        </div>
      </div>
      <div class="comment-body">
        <p>{{ comment.content }}</p>
      </div>
      <WarningBanner
        v-if="comment.isShow === false"
        title="留言"
        remove_reason="該留言因違反社群規範，已由管理員下架"
        :show_contact="true"
      />
      <div class="comment-footer" v-if="isArticleShow && !isCommentAuthor && comment.isShow !== false">
        <a :href="`${baseURL}complaint.html`" type="button">
          <i class="fa-regular fa-flag"></i>
          <span>檢舉</span>
        </a>
      </div>
    </article>
  </section>

  
</template>

<script>
import AuthorCard from '@/components/forum/authorCard.vue';
import WarningBanner from '@/components/WarningBanner.vue';

  export default {
    name: "CommentItem",

    components: {
      AuthorCard,
      WarningBanner
    },

    props: {
      comment: {
        type: Object,
        default: () => ({})
      },
      isArticleShow: {
        type: Boolean,
        default: true
      },
      currentMemberId: {
        type: [Number, String],
        default: null
      }
    },

    data(){
      return {
        baseURL: import.meta.env.BASE_URL
      }
    },

    computed: {
  isCommentAuthor() {
    return this.currentMemberId !== null
      && Number(this.currentMemberId) === Number(this.comment.memId);
      }
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.comment-item{
  display: flex;
  flex-direction: column;
  gap: 16px;
  // padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);

  padding: 16px;

  @include rwd("desktop"){
    flex-direction: row;
    gap: 8px;
    padding: 0;
    
    background-color: transparent;
    border-radius: 0;
    box-shadow: none;
  }

  i {
    margin-right: 4px;
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
.comment-content {
  width: 100%;
  display: contents;  // 消失外殼容器的特性 
  

  @include rwd("desktop"){
    display: flex;
    flex-direction: column;
    padding: 24px;
    background-color: #fff;
    border-radius: 8px;
    gap: 24px;
    box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);
  }

  img {
    // object-fit: contain;
    width: 100%;
  }

}
.comment-header {
  order: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-block: 12px;
  font-size: 14px;
  
  @include rwd("desktop") {
    border-bottom: 1px solid map-get($color, tertiary );

  }

  .floor-num {
    background-color: map-get($color, warmGray );
  }

  .time-post {
    color: map-get($color , neutral );
  }
}
.comment-body {
  order: 3;
}
.comment-footer {
  order: 4;
  display: inline-flex;
  justify-content: flex-end;
  padding-top: 16px;
  border-top: 1px solid #F7F5F3;

  a {
    color: map-get($color , neutral );
    cursor: pointer;
    font-size: 16px;
    font-weight: normal;
    line-height: 16px;

    &:hover {
      color: lighten(map-get($color , neutral ), 10%);
    }
  }
}
</style>