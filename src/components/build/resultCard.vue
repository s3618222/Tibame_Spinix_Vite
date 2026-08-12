<!-- resultCard.vue -->
<template>
  <div class="result-card">
    <div class="card-tag">當前配置</div>
    <div class="pic-logo">spinix</div>

    <div class="pics-container">
      <div class="part-item">
        <div class="part-img-box">
          <img src="/build/background-circle.png" alt="" class="bg-ring">
          <img src="/build/blade/BX-49 蒼龍突擊.png" alt="" class="preview-pic">
        </div>
        <div class="part-txt">
          <p class="part-title">戰刃</p>
          <p class="part-name">蒼龍突擊</p>
        </div>
        
      </div>
      <div class="part-item">
        <div class="part-img-box">
          <img src="/build/background-circle.png" alt="" class="bg-ring">
          <img src="/build/bit/UX-21-03 B.png" alt="" class="preview-pic">
        </div>
        <div class="part-txt">
          <p class="part-title">軸心</p>
          <p class="part-name">UX-21-03</p>
        </div>
      </div>
      <div class="part-item">
        <div class="part-img-box">
          <img src="/build/background-circle.png" alt="" class="bg-ring">
          <img src="/build/ratchet/CX-18-03.png" alt="" class="preview-pic">
        </div>
        <div class="part-txt">
          <p class="part-title">固鎖</p>
          <p class="part-name">CX-18-03</p>
        </div>
      </div>
      
      
    </div>

    

    <ul class="stat-list">
      <li class="stat-item">
        <span class="stat-label">攻擊</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 55%;"></span>
        </div>
        <span class="stat-value">55</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">防守</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 45%;"></span>
        </div>
        <span class="stat-value">45</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">持久</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 85%;"></span>
        </div>
        <span class="stat-value">90</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">重量(g)</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 38%;"></span>
        </div>
        <span class="stat-value">38</span>
      </li>
    </ul>

  </div>
</template>

<script>
export default {
  name: "ResultCard"
}
</script>

<style lang="scss" scoped>
@use "@/assets/scss/var" as *;
img {
  box-sizing: border-box;
}
.result-card {
  position: relative;
  width: 100%;
  background-color: map-get($color, secondary );
  border-radius: 12px;
  overflow: hidden;
  
}

.card-tag {
  color: map-get($color, secondary );
  font-weight: 600;
  font-size: 14px;
  padding: 4px 16px;
  position: absolute;
  top: 0;
  right: 0;
  background-color: map-get($color, secondary2 );
  border-radius: 0 0 0 12px;

}

.pic-logo {
  color: map-get($color, neutral );
}

.pics-container {
  justify-content: space-between;
  padding: 12px 12px;
  // padding-inline: 8px;
  width: 100%;
  min-width: 0;
  // background-color: map-get($color, secondary );
  // height: 200px;
  display: flex;
  gap: 12px;

  .part-item {
    font-size: 16px;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;

    .part-img-box {
      position: relative;
      width: 100%;
      max-width: 240px; 
      aspect-ratio: 1 / 1;

      display: flex;
      align-items: center;
      justify-content: center;  

      .bg-ring {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1; 
        animation: rotate 20s linear infinite; 
      }

      @keyframes rotate {
        from {
          rotate: 0deg;
        }

        to {
          rotate: 360deg;
        }
      }

      .preview-pic {
        position: relative;
        z-index: 2; 
        width: 60%; 
        height: 60%; 
      }

    }

    .part-txt {
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

  }
  

  .preview-pic {
    flex: 1;
    width: 60%;
    min-width: 0;
    height: auto;
    aspect-ratio: 1 / 1;
    display: block;
    object-fit: contain;
    
  }
}


  

.stat-list {
  color: map-get($color , white );
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.stat-item{
  display: flex;
  align-items: center;
  line-height: 14px;
  font-size: 14px;
  gap: 4px;

  .stat-bar {
    background-color: map-get($color , hint );
    height: 10px;
    flex: 1;
    border-radius: 5px;
    position: relative;
    overflow: hidden;
    
    .stat-bar-fill {
      display: inline-block;
      position: absolute;
      top: 0;left: 0;
      height: 100%;
      background-color: map-get($color, primary );
      
    }
  }
}
</style>