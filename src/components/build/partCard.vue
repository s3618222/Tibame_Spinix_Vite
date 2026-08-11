<template>
  <div class="part-card" @click="copyInfo">
    <button type="button" class="btn-info" @click.stop="showInfo">
      <i class="fa-solid fa-circle-info "></i>
    </button>
    <img :src="imgUrl" :alt="part.name" class="card-img">
    <p class="card-name">{{part.name}}</p>
<!-- 
    <div class="part-info">
      123
    </div> -->
  </div>

  

  
</template>

<script>
  export default {
    name: "PartCard",

    props: ["part"],

    computed: {
      imgUrl() {
        const baseUrl = import.meta.env.BASE_URL;
        // 確保 path 組合時不會多斜線或少斜線
        return `${baseUrl}${this.part.image.replace(/^\//, '')}`;
      }
    },

    methods: {
      showInfo(){
        alert("132");
      },
      copyInfo(){
        alert("哈哈");
      }
    }

  }
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.btn-info {
  position: absolute;
  top: 8px;
  right: 12px;
  cursor: pointer;
  z-index: 1;

  i {
    color: map-get($color , hint );
  }
}
.part-card {
  width: 100%;
  min-width: 0;
  aspect-ratio: 1 / 1;

  display: flex;
  flex-direction: column;
  justify-content: space-between;

  position: relative;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px;
  background: #fff;
  text-align: center;
  // cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    background-color: map-get($color, pending );
  }

  /* 被選中的卡片高亮黃框 */
  &.active {
    border-color: #fec96b;
    box-shadow: 0 0 0 1px #fec96b;
  }

  .card-img {
    position: relative;
    aspect-ratio: 1 / 1;
  }

  .card-name {
    margin-top: 8px;
    font-weight: bold;
    color: #333;
  }
}

.part-info {
  background-color: #fff;
  position: absolute;
  top: 30px;
  right: 0;
}
</style>