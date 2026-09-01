<template>
  <div class="part-card" :class="{ active: isSelected }" @click="selectCard">
    <button type="button" class="btn-info" @click.stop="toggleInfo">
      <i class="fa-solid fa-circle-info "></i>
    </button>
    <img v-if="!imgFailed" :src="imgUrl" :alt="part.name" class="card-img" @error="imgFailed = true">
    <i v-else class="fa-solid fa-image card-img-fallback"></i>
    <p class="card-name">{{part.name}}</p>

    <div 
      v-if="isInfoOpen" 
      class="info-backdrop" 
      @click.stop="closeInfo"
    ></div>

    <div class="part-info" v-if="isInfoOpen" @click.stop>
      <div class="title-part">
        <div class="name-part">{{ part.name }}</div>
        <div class="type-part">{{ part.type }}</div>
      </div>
      <div class="part-attrs">
        <div class="attr-part">
        攻
        <span class="num-part">{{ part.atk }}</span>
      </div>
      <div class="attr-part">
        防
        <span class="num-part">{{ part.def }}</span>
      </div>
      <div class="attr-part">
        持
        <span class="num-part">{{ part.sta }}</span>
      </div>
      <div class="attr-part">
        重
        <span class="num-part">{{ part.wei }}</span>
      </div>
      </div>
      
    </div>
  </div>

  

  
</template>

<script>
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";
  export default {
    name: "PartCard",

    data(){
      return {
        isInfoOpen: false,
        imgFailed: false // 記錄這張圖片是否載入失敗（實體檔案不存在導致 404）
      }
    },

    props: {
      part: { type: Object, required: true },
      selectedId: { type: Number, default: null }
    },

    computed: {
      imgUrl() {
        const baseUrl = import.meta.env.BASE_URL;
        const pic = this.part.image;
        if (pic.startsWith("uploads/beyblade/")) {
          return `${phpBaseUrl}/${pic}`;
        }

        return `${baseUrl}${pic.replace(/^\//, '')}`;
      },
      isSelected() {
        return this.part.id === this.selectedId;
      }
    },

    methods: {
      toggleInfo(){
        // alert("132");
        this.isInfoOpen = !this.isInfoOpen;
      },
      selectCard(){
        this.$emit('select', this.part);
      },
      closeInfo(){
        this.isInfoOpen = false;
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
  transition: all .2s;
  opacity: 0;

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
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    background-color: map-get($color, pending );
    
    .btn-info { opacity: 1;}
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
  
  .card-img-fallback {
    width: 100%;
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: map-get($color, hint);
  }

  .card-name {
    margin-top: 8px;
    font-weight: bold;
    color: #333;
  }
}

.info-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 9; /* 設在 9 */
  background: transparent;
  cursor: auto;
}

.part-info {
  font-size: 14px;
  width: 80%;
  min-width: 116px;
  background-color: #fff;
  cursor: auto;
  position: absolute;
  top: 30px;
  right: 0;
  z-index: 10;
  
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 8px;
  border-radius: 12px;
  background-color: map-get($color , tertiary);
  // border: 1px solid map-get($color , black);
  color: map-get($color, secondary );
  box-shadow: 2px 2px 10px 2px rgba(0, 0, 0, 0.1);
  

  .title-part {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 6px;

    .name-part {
      font-size: 16px;
      font-weight: 600;
    }
  }

  .part-attrs {
    display: flex;
    justify-content: space-between;
  }

  .attr-part {
    display: flex;
    flex-direction: column;
    gap: 4px;

    .num-part {
      color: map-get($color , secondary2);
      font-weight: 500;
    }
  }

  
}
</style>