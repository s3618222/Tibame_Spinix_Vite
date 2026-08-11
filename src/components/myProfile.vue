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
          <img class="avatar" :src="profile.avatar" alt="使用者頭像" />
          <button type="button" class="avatar-edit-link">
            更換頭貼
            <i class="fa-regular fa-pen-to-square"></i>
          </button>
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
            <div class="form-group">
              <label>聯絡信箱</label>
              <div class="readonly-box">{{ profile.email }}</div>
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
          <p class="stat-label">參賽次數</p>
          <p class="stat-number">12</p>
          <div class="stat-bar"></div>
        </div>
        <div class="stat-item">
          <p class="stat-label">勝場次數</p>
          <p class="stat-number">3</p>
          <div class="stat-bar"></div>
        </div>
        <div class="stat-item">
          <p class="stat-label">勝率</p>
          <p class="stat-number">25%</p>
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
      //帳戶資料假資料，先寫死
      profile: {
        avatar: "/spinix_member_default.png",
        account: "bill0714",
        username: "陀螺戰神123",
        email: "bill0714@example.com",
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
      ]
    };
  },

  computed: {
    passwordMask() {
      return "*".repeat(9);
    }
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
