<template>
  <section class="my-profile">
    <!-- 頁首 -->
    <div class="profile-header">
      <div class="profile-header-text">
        <h1>個人資料</h1>
        <p>管理你的個人資料設定、聯絡資訊、戰績與成就</p>
      </div>
      <button type="button" class="edit-btn">
        編輯個人資料
        <i class="fa-regular fa-pen-to-square"></i>
      </button>
    </div>

    <!-- 帳戶資料 -->
    <section class="profile-card account-card">
      <h2><i class="fa-regular fa-address-card"></i>帳戶資料</h2>

      <div class="account-body">
        <div class="avatar-block">
          <img 
            class="avatar" 
            :src="avatarPreview || getMemberAvatarUrl(profile.avatar)" 
            alt="使用者頭像" 
          />
          <button 
            type="button" 
            class="avatar-edit-link"
            @click="$refs.avatarInput.click()"
          >
            更換頭貼
            <i class="fa-regular fa-pen-to-square"></i>
          </button>
          <input
            ref="avatarInput"
            type="file"
            accept="image/jpeg,image/png"
            hidden
            @change="handleAvatarChange"
          />

          <!-- 有選新圖片時才出現操作按鈕 -->
          <div
            v-if="avatarFile"
            class="avatar-confirm-actions"
          >
            <button
              type="button"
              class="cancel-avatar-btn"
              @click="cancelAvatarChange"
            >
              取消
            </button>

            <button
              type="button"
              class="save-avatar-btn"
              @click="uploadAvatar"
            >
              確認更新
            </button>
          </div>
        </div>

        <div class="account-form">
          <div class="form-row">
            <div class="form-group">
              <label>帳號 <span class="hint">(註冊後無法更改)</span></label>
              <div class="readonly-box">{{ profile.account }}</div>
            </div>
            <div class="form-group">
              <label>密碼</label>
              <div class="readonly-box">{{ passwordMask }}</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>使用者名稱</label>
              <div class="readonly-box">{{ profile.username }}</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>手機號碼</label>
              <div class="readonly-box">{{ profile.phone }}</div>
            </div>
            <div class="form-group">
              <label>市話(選填)</label>
              <div class="readonly-box">{{ profile.landline || "—" }}</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>出生日期</label>
              <div class="birthday-group">
                <div class="readonly-box">{{ profile.birthYear }}</div>
                <div class="readonly-box">{{ profile.birthMonth }}</div>
                <div class="readonly-box">{{ profile.birthDay }}</div>
              </div>
            </div>
            <div class="form-group">
              <label>性別</label>
              <div class="gender-group">
                <div
                  v-for="option in genderOptions"
                  :key="option.value"
                  class="gender-box"
                  :class="{ active: profile.gender === option.value }"
                >
                  {{ option.label }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 戰鬥統計 -->
    <section class="profile-card stats-card">
      <h2><i class="fa-solid fa-trophy"></i>戰鬥統計</h2>

      <div class="stats-grid">
        <div class="stat-item">
          <p class="stat-label">約戰總場次</p>
          <p class="stat-number">{{ battleStats.totalBattles }}</p>
          <div class="stat-bar"></div>
        </div>
        <div class="stat-item">
          <p class="stat-label">競技場次</p>
          <p class="stat-number">{{ battleStats.competitiveTotal }}</p>
          <div class="stat-bar"></div>
        </div>
        <div class="stat-item">
          <p class="stat-label">競技勝率</p>
          <p class="stat-number">{{ battleStats.winRate }}</p>
          <div class="stat-bar"></div>
        </div>
      </div>
    </section>
  </section>
</template>

<script>
  export default {
    name: "MyProfile",

    data() {
      return {
        avatarFile: null,
        avatarPreview: "",
        
        //帳戶資料假資料，先寫死
        profile: {
          avatar: "spinix_member_default.png",
          account: "",
          username: "",
          phone: "0912345678",
          landline: "",
          birthYear: "1999",
          birthMonth: "06",
          birthDay: "06",
          gender: "male" // "male" | "female" | "secret"
        },

        genderOptions: [
          { value: "male", label: "男" },
          { value: "female", label: "女" },
          { value: "secret", label: "保密" }
        ],

        //當前會員約戰相關統計資料
        battleStats: {
          totalBattles: 0,
          competitiveTotal: 0,
          winRate: null
        }
      };
    },

    computed: {
      phpBaseUrl() { //判斷目前php的執行環境，調整網址前綴
        return (
          location.hostname === "localhost" ||
          location.hostname === "127.0.0.1"
            ? "http://localhost:8888/Spinix/php"
            : "/ckd101/g2/php"
        );
      },

      baseUrl() {
        return import.meta.env.BASE_URL;
      },

      passwordMask() {
        return "*".repeat(9);
      }
    },

    methods: {
      handleAvatarChange(e) { //會員頭像上傳預覽
        const file = e.target.files[0];

        if(!file) {
          return;
        }

        //限制圖片上傳格式
        const allowedTypes = [
          "image/jpeg",
          "image/png"
        ];

        if (!allowedTypes.includes(file.type)) {
          alert("會員頭像僅支援 JPG、PNG 格式");
          e.target.value = "";
          return;
        }

        //圖片最大限制2MB
        const maxFileSize = 2 * 1024 * 1024;

        if (file.size > maxFileSize) {
          alert("會員頭像大小不可超過 2MB");
          e.target.value = "";
          return;
        }

        //儲存圖片檔，供後續上傳API使用
        this.avatarFile = file;

        //建立供瀏覽器預覽的網址
        this.avatarPreview = URL.createObjectURL(file);
      },

      async uploadAvatar() { //串聯後端API，將會員上傳的頭像圖片傳回給後端

        // 如果還沒有選新圖片，就不執行上傳
        if (!this.avatarFile) {
          alert("請先選擇要更換的會員頭像");
          return;
        }

        const formData = new FormData();
        formData.append("avatar", this.avatarFile);

        try {

          const response = await fetch(`${this.phpBaseUrl}/member/member_avatar_update.php`, {
            method: "POST",
            credentials: "include",
            body: formData
          });

          const data = await response.json();

          // API 回傳失敗時
          if (!response.ok || !data.success) {
            alert(data.message || "會員頭像更新失敗");
            return;
          }

          // 頭像上傳成功
          alert(data.message);

          // 將後端回傳的新頭像路徑，更新放入前端
          this.profile.avatar = data.photo;

          //新頭像更新放入前端後，就可以把原先的預覽圖清空
          this.avatarPreview = "";

          //清除原先等待被上傳的File
          this.avatarFile = null;

          // 同時清掉 file input，讓之後選同一張圖片也能再次觸發 change
          if (this.$refs.avatarInput) {
            this.$refs.avatarInput.value = "";
          }

          console.log("會員頭像更新成功：", data.photo);

        } catch (error) {
          
          console.error("會員頭像更新失敗：", error);
          alert("會員頭像更新失敗，請稍後再試");

        }

      },

      getMemberAvatarUrl(photo) { //根據當前執行環境，判斷圖片路徑

        // 當資料庫沒有頭像時，就使用平台預設圖
        if (!photo) {
          return (
            this.baseUrl + "spinix_member_default.png"
          );
        }

        // 當頭像為會員自行上傳的動態圖片
        if (photo.startsWith("uploads/member/")) {
          return `${this.phpBaseUrl}/${photo}`;
        }

        // 當頭像是原先放在public中的靜態預設圖
        return this.baseUrl + photo;
      },

      cancelAvatarChange() { //取消頭像更新函式
        // 清除目前建立的預覽網址
        if (this.avatarPreview) {
          URL.revokeObjectURL(this.avatarPreview);
        }

        // 清掉當前選擇的新圖片
        this.avatarFile = null;
        this.avatarPreview = "";

        // 清空 input，讓之後即使重新選同一張圖也可以再次觸發 change
        if (this.$refs.avatarInput) {
          this.$refs.avatarInput.value = "";
        }
      },

      async fetchCurrentMember() { //取得當前會員基本資料
        try {
          const response = await fetch(`${this.phpBaseUrl}/member/currentMember_get.php`, {
            credentials: "include"
          });

          const data = await response.json();

          if (!response.ok || !data.success || !data.isLoggedIn) {
            console.error(data.message || "目前沒有登入會員");
            return;
          }

          this.profile.avatar = data.member.photo || "spinix_member_default.png";
          this.profile.account = data.member.account;
          this.profile.username = data.member.name;
          console.log("目前登入會員資料：", data.member);

        } catch (error) {
          console.error("取得會員資料失敗：", error);
        }
      },

      async fetchBattleStats() { //串接取得會員約戰統計數據API
        try {
          const response = await fetch(`${this.phpBaseUrl}/member/my_battle_stats_get.php`, {
            credentials: "include"
          });

          const data = await response.json();

          if (!response.ok || !data.success) {
            console.error(data.message);
            return;
          }

          this.battleStats = data.stats;

        } catch (error) {
          console.error("取得約戰統計失敗：", error);
        }
      }
    },

    mounted() {
      this.fetchCurrentMember();
      this.fetchBattleStats();
    }

  };
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .my-profile {
    width: 100%;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  //頁首
  .profile-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .profile-header-text {
    display: flex;
    flex-direction: column;
    gap: 12px;

    h1 {
      font-size: map.get($fontSize, h1);
      font-weight: 500;
      color: map.get($color, secondary2);
    }

    p {
      font-size: 16px;
      color: map.get($color, secondary);
    }
  }

  .edit-btn {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 16px 38px;
    border: 0;
    border-radius: 16px;
    background-color: map.get($color, primary);

    font-size: map.get($fontSize, h4);
    font-weight: 500;
    color: map.get($color, secondary);
    cursor: pointer;
    transition: background-color 0.2s ease;

    &:hover {
      background-color: darken(map.get($color, primary), 8%);
    }
  }

  //帳戶資料卡、戰鬥統計卡共用容器樣式
  .profile-card {
    width: 100%;
    padding: 32px;
    border-radius: 16px;
    background-color: map.get($color, white);
    box-shadow: 0 0 16px rgba(0, 0, 0, 0.15);

    display: flex;
    flex-direction: column;
    gap: 32px;

    h2 {
      display: flex;
      align-items: center;
      gap: 14px;

      font-size: map.get($fontSize, h2);
      font-weight: 500;
      color: map.get($color, secondary);
    }
  }

  //帳戶資料
  .account-body {
    display: flex;
    align-items: flex-start;
    gap: 32px;
  }

  .avatar-block {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
  }

  .avatar {
    width: 120px;
    height: 120px;
    border-radius: 24px;
    object-fit: cover;
  }

  .avatar-edit-link {
    display: flex;
    align-items: center;
    gap: 8px;

    border: 0;
    background: transparent;
    padding: 0;

    font-size: 14px;
    color: map.get($color, secondary);
    cursor: pointer;
  }

  //確認、取消更新頭像按鈕
  .avatar-confirm-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
  }

  .save-avatar-btn,
  .cancel-avatar-btn {
    height: 36px;
    padding: 0 12px;

    border-radius: 6px;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition:
      background-color 0.2s ease,
      border-color 0.2s ease,
      transform 0.2s ease;
  }

  // 確認更新：主要操作
  .save-avatar-btn {
    border: 1px solid #fec96b;

    color: #141c26;
    background-color: #fec96b;
  }

  .save-avatar-btn:hover {
    transform: translateY(-1px);
  }

  // 取消：次要操作
  .cancel-avatar-btn {
    border: 1px solid #141c26;

    color: #141c26;
    background-color: transparent;
  }

  .cancel-avatar-btn:hover {
    background-color: rgba(20, 28, 38, 0.06);
  }

  .account-form {
    flex: 1;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  .form-row {
    display: flex;
    align-items: flex-start;
    gap: 32px;
  }

  .form-group {
    flex: 1;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 4px;

    label {
      font-size: map.get($fontSize, default);
      color: map.get($color, secondary);

      .hint {
        color: map.get($color, hint);
      }
    }
  }

  .readonly-box {
    width: 100%;
    padding: 13px;
    border: 1px solid map.get($color, secondary);
    border-radius: 4px;

    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
  }

  .birthday-group {
    display: flex;
    gap: 8px;

    .readonly-box {
      flex: 1;
      min-width: 0;
      border-radius: 16px;
      text-align: center;
    }
  }

  .gender-group {
    display: flex;
    gap: 8px;
  }

  .gender-box {
    padding: 12px;
    border: 1px solid map.get($color, secondary);
    border-radius: 4px;

    font-size: 16px;
    color: map.get($color, secondary);
    background-color: map.get($color, white);

    &.active {
      background-color: map.get($color, secondary);
      color: map.get($color, white);
      border-color: map.get($color, secondary);
    }
  }

  //戰鬥統計
  .stats-grid {
    display: flex;
    gap: 28px;
  }

  .stat-item {
    padding: 32px;
    border: 1px solid map.get($color, secondary);
    border-radius: 4px;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }

  .stat-label {
    font-size: 16px;
    color: map.get($color, secondary);
  }

  .stat-number {
    font-size: 32px;
    font-weight: 700;
    color: map.get($color, secondary);
  }

  .stat-bar {
    width: 100%;
    height: 7px;
    background-color: map.get($color, secondary);
  }

  // ====================== RWD調整 ============================

  @media screen and (max-width: 900px) {
    .profile-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 16px;
    }

    .account-body {
      flex-direction: column;
      align-items: center;
    }

    .account-form {
      width: 100%;
    }

    .stats-grid {
      flex-wrap: wrap;
    }

    .stat-item {
      flex: 1 1 calc(50% - 14px);
    }
  }

  @media screen and (max-width: 576px) {
    .profile-card {
      padding: 24px;
    }

    .form-row {
      flex-direction: column;
      gap: 16px;
    }

    .stat-item {
      flex: 1 1 100%;
      padding: 20px;
    }
  }

  @media screen and (max-width: 375px) {
    .edit-btn {
      padding: 12px 20px;
      font-size: 16px;
    }

    .gender-group {
      flex-wrap: wrap;
    }
  }
</style>
