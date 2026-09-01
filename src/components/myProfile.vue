<template>
  <section class="my-profile">
    <!-- 頁首 -->
    <div class="profile-header">
      <div class="profile-header-text">
        <h1>個人資料</h1>
        <p>管理你的個人資料設定、聯絡資訊、戰績與成就</p>
      </div>
      <!-- 檢視模式：編輯按鈕；編輯模式：儲存 / 取消 -->
      <button
        v-if="!isEditing"
        type="button"
        class="edit-btn"
        @click="startEdit"
      >
        編輯個人資料
        <i class="fa-regular fa-pen-to-square"></i>
      </button>
      <div v-else class="edit-actions">
        <button type="button" class="cancel-btn" @click="cancelEdit">
          取消
        </button>
        <button type="button" class="edit-btn" :disabled="saving" @click="saveProfile">
          {{ saving ? "儲存中…" : "儲存" }}
          <i v-if="!saving" class="fa-regular fa-floppy-disk"></i>
        </button>
      </div>
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
              <button
                type="button"
                class="reset-password-link"
                @click="showResetModal = true"
              >
                重設密碼
                <i class="fa-solid fa-key"></i>
              </button>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>使用者名稱</label>
              <div v-if="!isEditing" class="readonly-box">{{ profile.username }}</div>
              <input
                v-else
                type="text"
                class="edit-box"
                maxlength="30"
                v-model="editForm.name"
                placeholder="輸入使用者名稱"
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>出生日期</label>
              <div v-if="!isEditing" class="birthday-group">
                <div class="readonly-box">{{ profile.birthYear }}</div>
                <div class="readonly-box">{{ profile.birthMonth }}</div>
                <div class="readonly-box">{{ profile.birthDay }}</div>
              </div>
              <input
                v-else
                type="date"
                class="edit-box"
                v-model="editForm.birth"
              />
            </div>
            <div class="form-group">
              <label>性別</label>
              <div class="gender-group">
                <div
                  v-for="option in genderOptions"
                  :key="option.value"
                  class="gender-box"
                  :class="{
                    active: isEditing
                      ? editForm.gender === option.value
                      : profile.gender === option.value,
                    editable: isEditing
                  }"
                  @click="isEditing && (editForm.gender = option.value)"
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
          <p class="stat-number">{{ battleStats.winRate ?? "-" }}</p>
          <div class="stat-bar"></div>
        </div>
      </div>
    </section>

    <!-- 重設密碼彈窗 -->
    <ResetPasswordModal
      v-if="showResetModal"
      @close="showResetModal = false"
      @success="onPasswordUpdated"
    />
  </section>
</template>

<script>
  import ResetPasswordModal from "@/components/member/ResetPasswordModal.vue";

  export default {
    name: "MyProfile",

    components: {
      ResetPasswordModal
    },

    data() {
      return {
        avatarFile: null,
        avatarPreview: "",

        //編輯模式狀態
        isEditing: false,
        saving: false,
        showResetModal: false,
        editForm: {
          name: "",
          gender: "",
          birth: "" // "Y-m-d"
        },

        //帳戶資料（由 currentMember_get.php 帶入）
        profile: {
          avatar: "spinix_member_default.png",
          account: "",
          username: "",
          birth: "", // 原始 "Y-m-d"，供編輯用
          birthYear: "",
          birthMonth: "",
          birthDay: "",
          gender: "" // "MALE" | "FEMALE"
        },

        genderOptions: [
          { value: "MALE", label: "男" },
          { value: "FEMALE", label: "女" }
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
          this.profile.gender = data.member.gender; // MALE / FEMALE
          this.profile.birth = data.member.birth || ""; // 原始 "Y-m-d"

          // 切分生日 "Y-m-d" → 年/月/日（缺值防呆）
          const [birthYear, birthMonth, birthDay] = (data.member.birth || "").split("-");
          this.profile.birthYear = birthYear || "—";
          this.profile.birthMonth = birthMonth || "—";
          this.profile.birthDay = birthDay || "—";

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
      },

      startEdit() { //進入編輯模式，帶入目前資料
        this.editForm.name = this.profile.username;
        this.editForm.gender = this.profile.gender;
        this.editForm.birth = this.profile.birth;
        this.isEditing = true;
      },

      cancelEdit() { //取消編輯，丟棄變更
        this.isEditing = false;
      },

      async saveProfile() { //儲存個人資料變更
        // 客端預檢
        if (!this.editForm.name.trim()) {
          alert("請填寫使用者名稱");
          return;
        }
        if (!this.editForm.gender) {
          alert("請選擇性別");
          return;
        }
        if (!this.editForm.birth) {
          alert("請選擇出生年月日");
          return;
        }

        this.saving = true;
        try {
          const payload = new FormData();
          payload.append("name", this.editForm.name.trim());
          payload.append("gender", this.editForm.gender);
          payload.append("birth", this.editForm.birth);

          const response = await fetch(`${this.phpBaseUrl}/member/member_profile_update.php`, {
            method: "POST",
            credentials: "include",
            body: payload
          });
          const data = await response.json();

          if (!response.ok || !data.success) {
            alert(data.message || "個人資料更新失敗");
            return;
          }

          // 用回傳更新畫面
          this.profile.username = data.member.name;
          this.profile.gender = data.member.gender;
          this.profile.birth = data.member.birth;

          const [birthYear, birthMonth, birthDay] = (data.member.birth || "").split("-");
          this.profile.birthYear = birthYear || "—";
          this.profile.birthMonth = birthMonth || "—";
          this.profile.birthDay = birthDay || "—";

          this.isEditing = false;
          alert(data.message);

        } catch (error) {
          console.error("個人資料更新失敗：", error);
          alert("個人資料更新失敗，請稍後再試");
        } finally {
          this.saving = false;
        }
      },

      onPasswordUpdated() { //重設密碼成功後
        this.showResetModal = false;
        alert("密碼已更新");
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

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
  }

  //編輯模式：儲存 + 取消
  .edit-actions {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .cancel-btn {
    padding: 16px 32px;
    border: 1px solid map.get($color, secondary);
    border-radius: 16px;
    background-color: transparent;

    font-size: map.get($fontSize, h4);
    font-weight: 500;
    color: map.get($color, secondary);
    cursor: pointer;
    transition: background-color 0.2s ease;

    &:hover {
      background-color: rgba(20, 28, 38, 0.06);
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

  //編輯模式輸入框（名稱 / 生日 date）
  .edit-box {
    width: 100%;
    padding: 13px;
    border: 1px solid map.get($color, secondary);
    border-radius: 4px;
    background-color: map.get($color, white);

    font: inherit;
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);

    &:focus {
      outline: none;
      border-color: map.get($color, secondary2);
    }
  }

  //重設密碼連結按鈕
  .reset-password-link {
    align-self: flex-start;
    margin-top: 4px;

    display: flex;
    align-items: center;
    gap: 6px;

    padding: 0;
    border: 0;
    background: transparent;

    font-size: 14px;
    color: map.get($color, secondary2);
    cursor: pointer;

    &:hover {
      text-decoration: underline;
    }
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

    //編輯模式：可點選
    &.editable {
      cursor: pointer;

      &:hover {
        border-color: map.get($color, secondary2);
      }
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
