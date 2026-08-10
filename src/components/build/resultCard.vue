<!-- resultCard.vue -->
<template>
  <div class="result-card">
    <div class="card-tag">當前配置</div>
    <div class="pic-logo">spinix</div>

    <div class="pics-container">
      <div class="blade-pre">
        <p class="part-title">戰刃</p>
        <div class="part-img-box">
          <img src="/public/build/blade/BX-49 蒼龍突擊.png" alt="" class="preview-pic">
        </div>
        <p class="part-name">蒼龍突擊</p>
      </div>
      <div class="blade-pre">
        <p class="part-title">戰刃</p>
        <img src="/public/build/blade/BX-49 蒼龍突擊.png" alt="" class="preview-pic">
        <p class="part-name">蒼龍突擊</p>
      </div>
      <div class="blade-pre">
        <p class="part-title">戰刃</p>
        <img src="/public/build/blade/BX-49 蒼龍突擊.png" alt="" class="preview-pic">
        <p class="part-name">蒼龍突擊</p>
      </div>
      
      
    </div>

    <ul class="spec-card-parts">
      <li><span class="label">戰刃</span><span class="val">X-Calibur</span></li>
      <li><span class="label">固鎖</span><span class="val">Magnum</span></li>
      <li><span class="label">軸心</span><span class="val">Impact</span></li>
    </ul>

    <ul class="stat-list">
      <li class="stat-item">
        <span class="stat-label">攻擊</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 85%;"></span>
        </div>
        <span class="stat-value">90</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">防守</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 85%;"></span>
        </div>
        <span class="stat-value">90</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">持久</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 85%;"></span>
        </div>
        <span class="stat-value">90</span>
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
    
    .blade-pre {
      

      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;

      .part-img-box {
        padding: 50px;
        background-image: url(/public/build/circle2.png);
        // background-size: cover;
        background-position: center;
        background-size: contain;
        // background-color: #fff;
        display: inline-flex;
        justify-self: center;
      }

    }
    padding-inline: 8px;
    width: 100%;
    min-width: 0;
    // background-color: map-get($color, secondary );
    // height: 200px;
    display: flex;
    gap: 4px;

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

  .spec-card-parts {
    color: map-get($color , white );
  }
  
  .stat-list {
    color: map-get($color , white );

  }
  .stat-item{
    display: flex;
  }
</style>