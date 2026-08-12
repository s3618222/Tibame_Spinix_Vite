<template>
  <section class="comment-item">
    <aside class="author-card">
      <AuthorCard />
    </aside>

    <article class="comment-content">
      <div class="comment-header">
        <div class="floor-num">樓層：2</div>
        <div class="time-post">
          <i class="fa-regular fa-clock"></i>
          <span>2026-05-12 15:38</span>
        </div>
      </div>
      <div class="comment-body">
        <p>X軸心確實很猛，但如果遇到防禦特化的對手，開局沒撞飛就幾乎等於輸了。我個人更傾向用 J (Jolt) 軸，稍微保留一點可控性，容錯率比較高。 Lorem ipsum dolor sit amet consectetur adipisicing elit. Ullam perferendis eligendi, reprehenderit asperiores doloremque fuga debitis iste eius rerum natus quibusdam magnam labore pariatur incidunt unde quia, adipisci, consectetur est?Lorem ipsum dolor sit, amet consectetur adipisicing elit. Tenetur quam reiciendis voluptatibus explicabo. Quaerat ad alias delectus voluptatem omnis quo praesentium, aperiam fugiat accusantium itaque explicabo facere sit. Quos, laudantium.</p>
      </div>
      <div class="comment-footer">
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

  export default {
    name: "CommentItem",

    components: {
      AuthorCard
    },

    data(){
      baseURL: import.meta.env.BASE_URL
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.comment-item{
  display: flex;
  flex-direction: column;
  // padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);

  @include rwd("desktop"){
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
.author-card {
  // order: 2;

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

.comment-footer {

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