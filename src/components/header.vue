

<template>
  <header
  :class="{
    solid: solid,
    scrolled: isScrolled
  }"
>
    <div class="header-main">
      <a href="homepage.html" class="logo">
        <img src="/spinix_logo.png" alt="Spinix Logo">
      </a>

      <nav :class="{active: isMenuOpen}">
        <ul>
          <li>
            <a :href="`${baseUrl}battle.html`" :class="{active: currentPath.toLowerCase().includes('battle')}">對戰配對</a>
          </li>

          <li>
            <a :href="`${baseUrl}build.html`" :class="{active: currentPath.toLowerCase().includes('build')}">陀螺配置</a>
          </li>

          <li>
            <a :href="`${baseUrl}forum.html`" :class="{active: currentPath.toLowerCase().includes('forum')}">Spinix論壇</a>
          </li>

          <li>
            <a :href="`${baseUrl}market.html`" 
            :class="{active: 
              currentPath.toLowerCase().includes('market') ||
              currentPath.toLowerCase().includes('product') ||
              currentPath.toLowerCase().includes('addchange')
            }"
          >
            交換專區</a>
          </li>
        </ul>
        <div class="header-btns" v-if="isChecked && !isLogin">
          <a class="signUp-btn" :href=" `${baseUrl}signUp.html`">註冊</a>
          <a class="signIn-btn" :href="`${baseUrl}signIn.html`">登入</a>
        </div>

      </nav>
      <!-- 登入後會員頭貼 -->
        <div class="member-center" ref="userMenuWrapper" v-if="isChecked && isLogin">
          <div class="header-user-headshot" @click="openUserPanel">
            <img 
              :src="`${baseUrl}${currentMember.photo}`"
              :alt="`${currentMember.name}的會員頭像`"
            >
          </div>
          
          <div class="user-panel"  :class="{ open: isUserPanelOpen }">
            <Transition :name="transitionName">
                <!-- 菜單面板 -->
              <div class="user-menu" v-if="currentPanel === 'menu'" key="menu">
                <div class="menu-user-info">
                  <div class="user-headshot">
                    <img 
                      :src="`${baseUrl}${currentMember.photo}`"
                      :alt="`${currentMember.name}的會員頭像`"
                    >
                  </div>
                  <p class="user-name">{{ currentMember.name }}</p>
                </div>
              <div class="menu-list">
                <ul>
                  <li>
                    <button type="button" class="menu-item" @click="goToPanel('notice')">
                        <i class="fa-solid fa-bell"></i>
                        <p class="item-label">會員通知</p>
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                  </li>
                  <li>
                    <a :href=" `${baseUrl}member.html`" class="menu-item">
                        <i class="fa-solid fa-user"></i>
                        <p class="item-label">會員中心</p>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                  </li>
                  <li class="btn-login">
                    <a href="#" class="menu-item " @click.prevent="signOut">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <p class="item-label">登出</p>
                    </a>
                  </li>
                </ul>
              </div>
              </div>


              <!-- 通知面板 -->
              <noticePanel 
                v-else-if="currentPanel === 'notice'" 
                key="notice"
                @close="goToPanel('menu')"
              />
            </Transition>

          </div>
        
  

        </div>
        <!-- 手機漢堡選單 -->
    <button 
      id="burger"
      type="button"
      :class="{ active: isMenuOpen, closing: isMenuClosing }" 
      @click="toggleMenu"
    >
      <span></span>
      <span></span>
      <span></span>
    </button>
    </div>
    
  </header>
</template>

<script>
import noticePanel from "./Userpanel.vue";
export default {
  name: "Header",

  components: {
    noticePanel   
  },


  props: {
    solid: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      baseUrl: import.meta.env.BASE_URL,
      isScrolled: false,
      isMenuOpen: false,
      isMenuClosing: false,
      isUserPanelOpen: false,
      isLogin:false, // 檢查是否登入
      isChecked: false, //用來記錄是否已確認登入狀態
      currentMember: null, //紀錄當下登入會員資料
      currentPanel: "menu",   // menu | notice
      transitionName: "slide-left",  // 控制滑動方向
      currentPath: window.location.pathname, //用來判斷當下所在位置在哪個分頁
    };
  },

  methods: {
    changeHeader() {
      // 固定實心的頁面不需要切換 scrolled
      if (this.solid) {
        this.isScrolled = false;
        return;
      }

      this.isScrolled = window.scrollY > 40;
    },
    // 控制手機板選單
    toggleMenu(){
      if (this.isMenuClosing) return; //避免動畫期間重複點擊

      // 開啟選單
      if (!this.isMenuOpen) {
        this.isMenuOpen = true;
        return;
      }

      // 關閉選單前，播放滾出動畫
      this.isMenuClosing = true;

      setTimeout(() => {
        this.isMenuOpen = false;
        this.isMenuClosing = false;
      }, 500);
    },

    // 打開會員中心面板
    openUserPanel(){
      this.isUserPanelOpen = !this.isUserPanelOpen;
      if (this.isUserPanelOpen) {
        this.currentPanel = "menu";
      }
    },
     // 點擊選單以外的地方要關閉
    handleClickOutside(e) {
      // this.$refs.userMenuWrapper 是包住頭貼+下拉選單的外層容器
      if (this.$refs.userMenuWrapper && !this.$refs.userMenuWrapper.contains(e.target)) {
        this.isUserPanelOpen = false;
      }
    },
    goToPanel(panelName) {
      // 進入通知面板 → 從右往左滑入
      // 返回選單面板 → 從左往右滑入 (退回去的感覺)
      this.transitionName = panelName === "notice" ? "slide-left" : "slide-right";
      this.currentPanel = panelName;
    },
    fetchCurrentMember() { //取得當前是否有登入、登入者資訊
      fetch("http://localhost:8888/Spinix/php/member/currentMember_get.php", {
        method: "GET",
        credentials: "include"
      }).then(res => res.json()).then(data => {
          this.isLogin = data.isLoggedIn;
          
          if (data.isLoggedIn) { //已登入狀態時，將登入會員的基本資訊存在currentMember變數中
            this.currentMember = data.member;
          } else {
            this.currentMember = null;
          }

          this.isChecked = true; //登入狀態檢查完畢後，將此變數更改為true，避免每次重新載入header時，都會在已登入/未登入UI間快速變動
        });
    },
    signOut() { //串接登出api
      fetch("http://localhost:8888/Spinix/php/member/signOut_post.php", {
        method: "POST",
        credentials: "include"
      }).then(res => res.json()).then(data => {
          if (data.success) { //登出成功後，跳轉回首頁
            window.location.href = `${this.baseUrl}homepage.html`;
          }
        });
    }
  },

  mounted() {
    this.fetchCurrentMember(); //一載入header時，即優先判斷是否有登入
    this.changeHeader();
    if (!this.solid) {
      window.addEventListener("scroll", this.changeHeader);
    }
    document.addEventListener("click", this.handleClickOutside);
  },

  beforeUnmount() {
    window.removeEventListener("scroll", this.changeHeader);
    document.removeEventListener("click", this.handleClickOutside);
  }
};


</script>

<style lang="scss" scoped>

  /* header高度88px，置頂後脫離文件流，
  須設定body {padding-top: 88px} 
  撐回原本高度 */

  //如果 Header 要全程實心，請在使用元件時傳入 solid：<Header solid />
  header {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;

      width: 100%;
      height: 88px;

      background-color: transparent;
      border-bottom: 1px solid transparent;
      display: flex;
      justify-content: space-between;
      align-items: center;

      transition:
          background-color 0.32s ease,
          border-color 0.32s ease,
          box-shadow 0.32s ease;
  }

  header.solid {
      background-color: #141C26;
  }

  header.scrolled {
      background-color: #141C26;
      border-bottom-color: rgba(255, 255, 255, 0.06);
      box-shadow: 0 8px 24px rgba(20, 28, 38, 0.12);
  }

  .header-main {
      display: flex;
      align-items: center;
      gap: 24px;
      width: 100%;
      padding-inline: 20px;
  }

  .header-main .logo {
      display: flex;
      align-items: center;
      flex-shrink: 0;
  }

  .header-main .logo img {
      display: block;
      width: 120px;
      transition: transform 0.32s ease;
      animation: logoSpin 0.7s ease;
  }

  .header-main .logo:hover img {
      transform: rotate(-16deg) scale(1.05);
  }

  /* logo旋轉動畫 */
  @keyframes logoSpin {
      from {
          transform: rotate(0deg);
      }

      to {
          transform: rotate(360deg);
      }
  }

  .header-main nav{
    width: 100%;
    display: flex;
    align-items: center; 
  }

  .header-main nav ul {
      // list-style: none;
      display: flex;
      flex: 1;
      gap: 24px;
  }

  .header-main nav ul li a {
      text-decoration: none;
      font-size: 18px;
      color: #ffffff;
      transition: color 0.28s ease;

      position: relative;
  }

  .header-main nav ul li a::after {
      content: "";
      position: absolute;
      left: 50%;
      bottom: -4px;
      transform: translateX(-50%);

      width: 0;
      height: 2px;
      background: #ffda94;
      transition: width 0.28s ease;
  }

  .header-main nav ul li a:hover,
  .header-main nav ul li a.active {
      color: #FEC96B;
  }

  .header-main nav ul li a:hover::after {
      width: 100%;
  }

  .header-btns {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 24px;
      // flex: 1;
  }

  .header-btns a {
      text-decoration: none;
      text-align: center;
      font-size: 18px;
      color: #ffffff;
      padding: 8px 24px;
      border-radius: 16px;

      transition:
          color 0.28s ease,
          background-color 0.28s ease,
          border-color 0.28s ease,
          box-shadow 0.28s ease,
          transform 0.28s ease;
  }

  .header-btns .signUp-btn {
      background-color: #FEC96B;
      color: #141C26;

      box-shadow: 0 4px 12px rgba(254, 201, 107, 0.12);
  }

  .header-btns .signUp-btn:hover {
      background-color: #ffda94;
      box-shadow: 0 7px 18px rgba(254, 201, 107, 0.2);
      transform: translateY(-2px);
  }

  .header-btns .signIn-btn {
      border: 1px solid rgba(255, 255, 255, 0.8);
      color: #ffffff;
  }

  .header-btns .signIn-btn:hover {
      border-color: #ffda94;
      color: #ffda94;
      transform: translateY(-2px);
  }

  .header-btns a:active {
      transform: translateY(0);
  }



  .header-main {
    .member-center{
      padding-right: 32px;

      .header-user-headshot{
        width: 50px;
        height: 50px;
        background-color: white;
        border-radius:50% ;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition:.3s ease;
        flex-shrink: 0;

        &:hover{
          border-color: #F29B00;
        }
        img{
          width: 100%;
        }
      }
      // 下拉選單
      .user-panel{
        background-color: #F7F5F3;
        border: 1px solid #dddddd;
        box-shadow: 0 8px 12px rgba(20, 28, 38, 0.1);
        position: absolute;
        right: 20px;
        margin-top: 20px;
        border-radius: 10px;
        min-width: 300px;
        height: 350px;
        display: flex;  
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;

        .user-menu{
          width: 100%;
          padding: 20px 12px;
          height: 100%;
          display: flex;              
          flex-direction: column;   
        }
        

        &.open {
          opacity: 1;
          visibility: visible;
          transform: translateY(0);
        }

        .slide-left-enter-active,
        .slide-left-leave-active,
        .slide-right-enter-active,
        .slide-right-leave-active {
          transition: transform 0.32s ease;   
          position: absolute;                  
          top: 0;
          left: 0;
          width: 100%;
        }

         // 前往通知面板：新面板從右邊滑進來，舊面板往左邊滑出去
        .slide-left-enter-from {
          transform: translateX(100%);
        }
        .slide-left-leave-to {
          transform: translateX(-100%);
        }

        // 返回選單面板：新面板從左邊滑進來，舊面板往右邊滑出去
        .slide-right-enter-from {
          transform: translateX(-100%);
        }
        .slide-right-leave-to {
          transform: translateX(100%);
        }

        .menu-user-info{
          display: flex;
          gap: 16px;
          align-items: center;
          flex-shrink: 0; 
          

          .user-headshot{
            width: 40px;
            border-radius: 50%;
            overflow: hidden;
            background-color: white;
            border: 1px solid #dddddd;
            
            img{
              width: 100%;
            }
          }
        }

        .menu-list{
          flex: 1;                // ⚠️ 確認這裡有沒有寫？
          min-height: 0;           // ⚠️ 確認這裡有沒有寫？（固定公式）
          
          height: 100%;
          ul{
            padding-top: 4px ;
            display: flex;
            flex-direction: column;
            height: 100%;

            li{  
              width: 100%;
              border-radius: 8px;
              transition: all .3s ease;
              margin-top: 12px;
              &:last-child {
                margin-top: auto;
              }
              

              &:hover{
                background-color: #fec96b;

                .menu-item{
                  color: #a86B00 ;
                }

                
              }

              .menu-item{
                display: flex;
                gap: 8px;
                padding: 8px 4px;
                align-items: center;
                justify-content: start;
                width: 100%;
                font-size: 18px;

                .item-label{
                  flex: 1;
                  text-align: left;
                  font-weight: lighter;
                  
                }
              }
              
            }
            
          }
        }
      }
    }
  }

  // 漢堡選單
  #burger{
    display: none;
    margin-right: 8px;
    width: 40px;
    height: 40px;
    padding: 0;
    border: none;
    background-color: transparent;

    position: relative;
    cursor: pointer;

    span{
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      transform-origin: center;

      width: 28px;
      height: 3px;
      background-color: #FEC96B;
      border-radius: 999px;

      transition:
        top 0.32s ease,
        width 0.32s ease,
        height 0.32s ease,
        transform 0.32s ease,
        background-color 0.24s ease,
        border-width 0.32s ease,
        border-color 0.32s ease,
        border-radius 0.32s ease;
    }

    span:nth-child(1) {
      top: 10px;
    }

    span:nth-child(2) {
      top: 18px;
    }

    span:nth-child(3) {
      top: 26px;
    }

    &.active {
      span:nth-child(1) {
        top: 18px;
        transform:
          translateX(-50%)
          rotate(45deg)
          scaleX(0.72);
      }

      span:nth-child(2) {
        top: 4px;

        width: 32px;
        height: 32px;

        background-color: transparent;
        border: 2px solid #FEC96B;
        border-radius: 50%;
      }

      span:nth-child(3) {
        top: 18px;
        transform:
          translateX(-50%)
          rotate(-45deg)
          scaleX(0.72);
      }
    }

    transition: transform .5s ease, opacity .24s ease;

    &.closing { 
      pointer-events: none;
      animation: burgerRollOut 0.5s ease-in forwards;
    }
  }

  //收起選單時的漢堡動畫
  @keyframes burgerRollOut {
    0% {
      transform: translateX(0) rotate(0deg);
      opacity: 1;
    }

    100% {
      transform: translateX(80px) rotate(360deg);
      opacity: 0;
    }
  }



  // RWD
  // 手機板
  @media screen and (width < 992px) {
    header{
      background-color: #141C26;
    }
    .header-main {
      gap: 16px;
      nav{      
        background-color: rgba(20, 28, 38, 0.88);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);

        border-left: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: none;

        height: calc(100vh - 88px);
        width: min(180px, 82vw);
        position: absolute;
        top: 88px;
        padding:28px 24px;
        right: 0;
        transform: translateX(100%);
        transition: transform 0.6s ease;
        flex-direction: column;
        justify-content: flex-start;
          &.active{
            transform: translateX(0);
            box-shadow: -12px 0 32px rgba(20, 28, 38, 0.22);
          }
          ul{
            width: 100%;
            text-align: center;
            flex-direction: column;
            flex: initial;
            gap: 28px;

            li {
              width: 100%;
              cursor: pointer;

              a {
                display: inline-block; 
              }

              &:hover a {
                color: #FEC96B;
              }

              &:hover a::after {
                width: 100%;
              }

            }
            
          }
          .header-btns{
            width: 80%;
            margin-top: 28px;
            flex-direction: column;
            align-items: stretch;
            gap: 20px;
            
          }
        }
      
        .logo{
          flex: 1;
        }
    }

    #burger{
      display: block;
      
    }
  }

  

</style>