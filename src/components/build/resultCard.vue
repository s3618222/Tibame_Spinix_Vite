<!-- resultCard.vue -->
<template>
  <div class="result-card">
    <div class="card-tag">當前配置</div>
    <div class="pic-logo">spinix</div>

    <div class="pics-container">
      <img src="/public/build/blade/BX-49 蒼龍突擊.png" alt="" class="preview-pic">
      <img src="/public/build/ratchet/BX-50-04.png" alt="" class="preview-pic">
      <img src="/public/build/bit/BX-50-02 TP.png" alt="" class="preview-pic">
      
    </div>

    <ul class="spec-card__parts">
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
        <span class="stat-label">攻擊</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" style="width: 85%;"></span>
        </div>
        <span class="stat-value">90</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">攻擊</span>
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
  }
  
  .card-tag {
    color: map-get($color, secondary );
    position: absolute;
    top: 0;
    right: 0;
    background-color: map-get($color, primary );
  }

  .pic-logo {
    color: map-get($color, neutral );
  }

  .pics-container {
    padding-inline: 8px;
    width: 100%;
    min-width: 0;
    background-color: map-get($color, secondary );
    // height: 200px;
    display: flex;
    gap: 4px;

    .preview-pic {
      flex: 1;
      width: 100%;
      min-width: 0;
      height: auto;
      aspect-ratio: 1 / 1;
      display: block;
      object-fit: contain;
      
    }
  }
  .stat-item{
    display: flex;
  }
</style>