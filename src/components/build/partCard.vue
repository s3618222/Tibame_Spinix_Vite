<template>
  <div class="part-card">
    <i class="fa-solid fa-circle-info btn-info"></i>
    <img :src="part.image" :alt="part.name" class="card-img">
    <p class="card-name">{{part.name}}</p>
    
  </div>

  
</template>

<script>
  export default {
    name: "PartCard",

    props: ["part"]
  }
</script>

<style lang="scss" scoped>
.btn-info {
  position: absolute;
  top: 0;
  right: 0;
  color: red;
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
  cursor: pointer;
  transition: all 0.2s ease;



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
</style>