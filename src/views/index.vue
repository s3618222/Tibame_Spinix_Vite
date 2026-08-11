<template>
  <main class="entry-page">

    <!-- 背景圖 -->
    <div 
      class="entry-bg"
      :style="{ backgroundImage: `url(${entryBg})` }"
    >
    </div>

    <!-- 主要內容 -->
    <section class="entry-main">

      <!-- Logo -->
      <div class="entry-logo">
        <img 
          :src="`${URL_BASE}spinix_logo_words.png`" 
          alt="Spinix Logo"
        >
      </div>

      <!-- 之後放人物與中央動畫 -->
      <div 
        class="entry-stage"
        :class="{ 'is-launching': stageState === 'launching' }"  
      >
        <button
          type="button" 
          class="entry-option entry-front"
          :class="{'is-hidden': stageState !== 'idle' && selectedTarget === 'back', 'is-selected': selectedTarget === 'front'}"
          @click="enterSite('front')"  
        >
            <img
              class="entry-player front"
              :src="frontPlayer"
              alt="前台入口人物"
            >
          <span class="entry-label">前台</span>
        </button>

        <button 
          type="button"
          class="entry-option entry-back"
          :class="{'is-hidden': stageState !== 'idle' && selectedTarget === 'front', 'is-selected': selectedTarget === 'back'}"
          @click="enterSite('back')" 
        >
          <img
            class="entry-player back"
            :src="backPlayer"
            alt="後台入口人物"
          >
          <span class="entry-label">後台</span>
        </button>

        <div
            v-if="stageState === 'shoot' || stageState === 'spinning'"
            class="launch-panel"
            :class="{
              'launch-panel--front': selectedTarget === 'back',
              'launch-panel--back': selectedTarget === 'front'
            }"
        >
          <p class="launch-title"  v-if="stageState === 'shoot'">GO SHOOT!</p>

          <!-- 陀螺圖 -->
          <img
            v-if="stageState === 'spinning'"
            class="entry-top"
            :src="entryTop"
            alt="戰鬥陀螺"
          >
          <div
            v-if="stageState === 'spinning'"
            class="loading-percent"
            :class="{ 'is-complete': loadingPercent === 100 }"
          >
            {{ loadingPercent }}%
          </div>
        </div>

      </div>

    </section>


    <!-- 智財權聲明 -->
    <footer class="entry-legal">
      <p>
        本網站為緯育TibaMe_前端工程師班第100期學員專題成果作品,
        本平台僅供學習、展示之用。若有抵觸有關著作權,
        或有第三人主張侵害智慧財產權等情事,
        均由學員負法律上責任。若有侵權疑慮,
        您可以私訊[緯育TibaMe],
        後續會由專人協助處理。
      </p>
    </footer>

  </main>
</template>


<script>
  export default {
    name: "EntryView",

    data() {
      return {
        URL_BASE: import.meta.env.BASE_URL,
        entryBg: `${import.meta.env.BASE_URL}entry_arena.jpg`,
        frontPlayer: `${import.meta.env.BASE_URL}entry_player_front.png`,
        backPlayer: `${import.meta.env.BASE_URL}entry_player_back.png`,
        entryTop: `${import.meta.env.BASE_URL}entry_top.png`,

        stageState: "idle",
        selectedTarget: null,

        loadingPercent: 0,
        loadingTimer: null,
      };
    },

    methods: {
      enterSite(target) {
        if (this.stageState !== "idle") return; //避免重複點擊

        this.selectedTarget = target;
        this.stageState = "launching";
        console.log(this.selectedTarget, this.stageState);

        setTimeout(() => {
          this.stageState = "shoot";
        }, 450);

        setTimeout(() => {
          this.stageState = "spinning";
        }, 1400);

        setTimeout(() => {
          this.startLoading();
        }, 2200);
      },

      startLoading() { // 轉動百分比計算；每35ms + 2，約1.75s完成
        this.loadingPercent = 0;

        this.loadingTimer = setInterval(() => {
          this.loadingPercent += 3;

          if (this.loadingPercent >= 100) { //進度條到100後，停0.3s再跳頁
            this.loadingPercent = 100;

            clearInterval(this.loadingTimer);
            this.loadingTimer = null;

            setTimeout(() => {
              this.goToTarget();
            }, 300);
          }
        }, 35);
      },

      goToTarget() { //跳轉分頁函式
        if (this.selectedTarget === "front") {
          window.location.href = `${this.URL_BASE}homepage.html`; // !!!之後首頁檔案名稱要改homepage
        }

        if (this.selectedTarget === "back") {
          window.location.href = `${this.URL_BASE}backMember.html`; //!!!後台登入頁還沒做，所以先直接進到後台頁面
        }
      },

      wait(ms) { //動畫執行順序控制
        return new Promise((resolve) => {
          setTimeout(resolve, ms);
        });
      },

    },
  };
</script>


<style lang="scss" scoped>
  .entry-page {
    width: 100%;
    min-height: 100vh;

    display: flex;
    flex-direction: column;

    overflow-x: hidden;
    position: relative;

    background-color: #141c26;
    color: #ffffff;
  }

  //背景場地圖
  .entry-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
  }

  //場地圖遮罩
  .entry-bg::after {
    content: "";
    position: absolute;
    inset: 0;
    
    background:
      //由內往外的擴散漸層
      radial-gradient(
        circle at center,
        rgba(100, 116, 139, 0.12) 0%,
        rgba(20, 28, 38, 0.22) 45%,
        rgba(20, 28, 38, 0.82) 100%
      ),
    rgba(20, 28, 38, 0.58);
  }


  .entry-main {
    flex: 1;

    min-height: 0;

    position: relative;
    z-index: 1;

    display: flex;
    flex-direction: column;
    margin-bottom: 8px;
  }

  //spinix logo
  .entry-logo {
    width: 100%;
    display: flex;
    justify-content: center;

    padding-top: 48px;

    img {
      display: block;
        width: 172px;
    }
  }

  .entry-stage {
    flex: 1;
    min-height: 0;
    
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 120px;
  }

  //前後台版位
  .entry-option {
    position: relative;
    isolation: isolate; //指讓前後台的index只作用在.entry-option內，不影響外層背景

    width: 320px;
    height: 420px;

    display: flex;
    justify-content: center;
    align-items: flex-end;

    padding-bottom: 28px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    background: transparent;
    color: #ffffff;
    font: inherit;
    cursor: pointer;

    transition:
      border-color 0.32s ease,
      color 0.32s ease,
      opacity 0.32s ease,
      filter 0.32s ease,
      transform 0.32s ease;
    
    &:hover {
        border-color: #FEC96B;
        color: #FEC96B;
        transform: translateY(-6px);
    }
  }

  //偽元素製作光暈效果
  .entry-option::before {
      content: "";
      position: absolute;
      inset: 18% 8% 8%;
      border-radius: 50%;

      background: radial-gradient(
        circle,
        rgba(254, 201, 107, 0.24) 0%,
        rgba(254, 201, 107, 0.08) 45%,
        rgba(254, 201, 107, 0) 75%
      );

      opacity: 0;
      transition: opacity 0.35s ease;
      z-index: -1;
    }

    //hover至前、後台時，顯示微微光暈
    .entry-option:hover::before {
      opacity: 1;
    }

  //前後台選項中，沒有被hover的一方暗化
  .entry-stage:has(.entry-option:hover) .entry-option:not(:hover) {
      filter: brightness(0.55);
      color: #64748B;
      border-color: rgba(100, 116, 139, 0.35);
  }

  .entry-placeholder {
    color: rgba(255, 255, 255, 0.2);

    font-size: 18px;
    letter-spacing: 0.2em;
  }

  //前後台人物圖
  .entry-player {
    width: 100%;
    max-height: 360px;

    position: absolute;
    left: 50%;
    bottom: 54px;
    transform: translateX(-50%);
    
    object-fit: contain;
    pointer-events: none;
    z-index: 1;

    transition:
      filter 0.32s ease,
      transform 0.32s ease;
  }

  //前台人物
  .entry-option .entry-player {
    width: 100%;
  }

  //後台人物
  .entry-option .entry-player {
    width: 100%;
  }

  .entry-option:hover .entry-player {
    filter:
      brightness(1.35)
      sepia(1)
      saturate(2.2)
      hue-rotate(355deg);
  }

    //沒被選中的選項淡出
  .entry-option.is-hidden {
    opacity: 0;
    filter: none;
    pointer-events: none;
  }

  //選中的一方防止再次被點擊
  .entry-stage.is-launching .entry-option {
    pointer-events: none;
  }

  //被選中後的樣式
  .entry-option.is-selected {
    border-color: #FEC96B;
    color: #FEC96B;

    transform: translateY(-6px);
    box-shadow:
      0 0 0 1px rgba(254, 201, 107, 0.25),
      0 0 28px rgba(254, 201, 107, 0.18);

    cursor: default;
  }

  //原先hover時出現的光暈，在選中後也維持留下
  .entry-option.is-selected::before {
    opacity: 1;
  }

  //選中人物維持亮化
  .entry-option.is-selected .entry-player {
    filter:
      brightness(1.35)
      sepia(1)
      saturate(2.2)
      hue-rotate(355deg);
  }

  //前後台文字標籤
  .entry-label {
    position: relative;
    z-index: 2;

    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.12em;

    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
  }

  //go shoot字樣
  .launch-panel {
    position: absolute;

    width: 320px;
    height: 420px;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    pointer-events: none;
  }

  //百分比進度文字
  .loading-percent {
    margin-top: 20px;

    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.08em;

    color: #FEC96B;

    text-shadow:
      0 0 10px rgba(254, 201, 107, 0.3);

    transition:
      transform 0.24s ease,
      text-shadow 0.24s ease;
  }

  .loading-percent.is-complete {
    transform: scale(1.18);

    text-shadow:
      0 0 12px rgba(254, 201, 107, 0.65),
      0 0 28px rgba(254, 201, 107, 0.45);
  }

  .launch-title {
    margin: 0;

    font-size: 38px;
    font-weight: 800;
    letter-spacing: 0.08em;

    color: #FEC96B;

    text-shadow:
      0 0 12px rgba(254, 201, 107, 0.35),
      0 0 28px rgba(254, 201, 107, 0.18);
  }

  .launch-panel--front {
    transform: translateX(-220px);
  }

  .launch-panel--back {
    transform: translateX(220px);
  }

  //智財區
  .entry-legal {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 16px 24px;
    background-color: rgba(10, 15, 21, 0.88);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  .entry-legal p {
    max-width: 800px;

    margin: 0 auto;

    font-size: 13px;
    text-align: center;
    line-height: 1.7;

    color: rgba(255, 255, 255, 0.58);
  }

  //陀螺圖
  .entry-top {
    width: 180px;
    height: 180px;

    object-fit: contain;
    object-position: center;

    transform-origin: center center;

    animation:
      topIn 0.3s ease-out both,
      topSpin 0.6s linear 0.5s infinite;
  }

  //陀螺漸入動畫
  @keyframes topIn {
    from {
      opacity: 0;
      transform: scale(0.65);
    }

    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  //陀螺旋轉動畫
  @keyframes topSpin {
    from {
      rotate: 0deg;
    }

    to {
      rotate: 360deg;
    }
  }


// ======================= RWD調整 =============================

  @media (max-width: 992px) {
    .entry-stage {
      gap: 72px;
    }

    .entry-option {
      width: 280px;
      height: 380px;
    }

    .entry-player {
      max-height: 325px;
    }

    .entry-logo {
      padding-top: 40px;

      img {
        width: 155px;
      }
    }

    .entry-label {
      font-size: 22px;
    }

    .launch-title {
      font-size: 34px;
    }

    .launch-panel {
      width: 280px;
      height: 380px;
    }

    .launch-panel--front {
      transform: translateX(-176px);
    }

    .launch-panel--back {
      transform: translateX(176px);
    }
  }

  @media (max-width: 768px) {
    .entry-stage {
      gap: 40px;
    }

    .entry-option {
      width: 240px;
      height: 340px;
    }

    .entry-player {
      max-height: 290px;
    }

    .entry-label {
      font-size: 20px;
    }

    .entry-logo {
      padding-top: 32px;

      img {
        width: 145px;
      }
    }

    .launch-title {
      white-space: nowrap;
      font-size: 30px;
    }

    .launch-panel {
      width: 240px;
      height: 340px;
    }

    .launch-panel--front {
      transform: translateX(-140px);
    }

    .launch-panel--back {
      transform: translateX(140px);
    }

    .entry-top {
      width: 155px;
      height: 155px;
    }

    .loading-percent {
      font-size: 22px;
    }

  }

  @media (max-width: 576px) {
    .entry-stage {
      gap: 20px;
    }

    .entry-option {
      width: 190px;
      height: 300px;
    }

    .entry-player {
      max-height: 250px;
    }

    .entry-label {
      font-size: 18px;
    }

    .entry-logo {
      padding-top: 24px;

      img {
        width: 130px;
      }
    }

    .launch-panel {
      width: 190px;
      height: 300px;
    }

    .entry-top {
      width: 130px;
      height: 130px;
    }

    .loading-percent {
      font-size: 20px;
    }

    .launch-title {
      font-size: 24px;
      white-space: nowrap;
    }

    .launch-panel--front {
      transform: translateX(-105px);
    }

    .launch-panel--back {
      transform: translateX(105px);
    }

    .entry-player.back {
      bottom: 68px;
    }
  }

  @media (max-width: 430px) {

    .entry-stage {
      gap: 12px;
    }

    .entry-option {
      width: 170px;
      height: 275px;
    }

    .entry-player {
      max-height: 225px;
    }

    .entry-label {
      font-size: 16px;
    }

    .entry-logo {
      padding-top: 20px;

      img {
        width: 118px;
      }
    }

    .launch-panel {
      width: 170px;
      height: 275px;
    }

    .entry-top {
      width: 115px;
      height: 115px;
    }

    .loading-percent {
      margin-top: 14px;
      font-size: 18px;
    }

    .launch-title {
      font-size: 20px;
      white-space: nowrap;
    }
  }

</style>