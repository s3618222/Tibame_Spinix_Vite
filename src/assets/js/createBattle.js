import { createApp, useCssVars } from "vue";
import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";

createApp(Header, {
  solid: true
}).mount("#headerApp");

createApp(Footer).mount("#footerApp");

//重要!!! 判斷當前環境
const phpBaseUrl =
  location.hostname === "localhost" ||
    location.hostname === "127.0.0.1"
    ? "http://localhost:8888/Spinix/php"
    : "/ckd101/g2/php";

let currentMember = null;

// 檢查使用者是否已登入會員，否則無法進入建立對戰頁
function checkCreateBattleLogin() {
  fetch(`${phpBaseUrl}/member/currentMember_get.php`, {
    credentials: "include"
  }).then(res => res.json()).then(data => {

    if (!data.success || !data.isLoggedIn) {
      alert("請先登入會員，再建立對戰邀約");
      window.location.href = `${import.meta.env.BASE_URL}signIn.html`;
      return;
    }

    // 保存目前登入會員資料
    currentMember = data.member;
    console.log("目前建立約戰的會員：", currentMember);

  }).catch(error => {
    console.error("登入狀態確認失敗：", error);
  });
}

checkCreateBattleLogin();

const battleCoverInput = document.querySelector("#battleCover"); //封面圖上船欄位
const coverPreviewImage = document.querySelector("#coverPreviewImage"); //封面圖預覽
const coverUploadPlaceholder = document.querySelector(".cover-upload-placeholder"); //封面上傳文字說明

battleCoverInput.addEventListener("change", function () {
  //抓取使用者上傳的圖片
  const selectedFile = this.files[0];

  // 若沒有上傳圖片，則停止後續動作
  if (!selectedFile) {
    return;
  }

  //建立閱讀器
  const reader = new FileReader();

  // 等上傳圖片讀取完成後，再執行將預覽區顯示更換為使用者上傳的圖片
  reader.addEventListener("load", function () {
    coverPreviewImage.src = reader.result;
    coverPreviewImage.hidden = false;
    coverUploadPlaceholder.hidden = true;
  });

  // 開始讀取圖片
  reader.readAsDataURL(selectedFile);
});

const battleCitySelect = document.querySelector("#battleCity"); //縣市select
const battleDistrictSelect = document.querySelector("#battleDistrict"); //行政區select

//向後端資料庫取得縣市資料
function fetchCities() {
  fetch(`${phpBaseUrl}/location/cities_get.php`).then(res => res.json()).then(data => {
    data.forEach(city => {
      const option = document.createElement('option');

      option.value = city.CITY_ID;
      option.textContent = city.CITY_NAME;
      battleCitySelect.append(option);
    });
  });
}

fetchCities();

//根據使用者所選縣市，再向後端取得對應的行政區資料
function fetchDistricts(cityId) {
  fetch(`${phpBaseUrl}/location/districts_get.php?city_id=${cityId}`).then(res => res.json()).then(data => {
    data.forEach(district => {
      const option = document.createElement('option');

      option.value = district.DISTRICT_ID;
      option.textContent = district.DISTRICT_NAME;

      battleDistrictSelect.append(option);
    });
  });
}

//監聽縣市select，當縣市選項切換時，重新render行政區選項
battleCitySelect.addEventListener('change', function () {
  //先清除舊的行政區選項
  battleDistrictSelect.innerHTML = `<option value="">選擇行政區</option>`;

  //使用者如果沒選擇縣市時，行政區欄位要維持disabled
  if (!this.value) {
    battleDistrictSelect.disabled = true;
    return;
  }

  //有選擇縣市時，行政區欄位恢復可選狀態，且呼叫行政區api
  battleDistrictSelect.disabled = false;
  fetchDistricts(this.value);
})

const formView = document.querySelector(".create-view--form"); //填寫表單區塊(含進度瀏覽)
const battleForm = document.querySelector("#battleForm"); //邀約建立表單
const previewView = document.querySelector("#battlePreview"); // 邀約卡預覽區
const previewBtn = document.querySelector("#previewBtn"); // 預覽按鈕
const returnEditBtn = document.querySelector("#returnEditBtn"); // 返回修改按鈕
const confirmCreateBtn = document.querySelector("#confirmCreateBtn"); //確認建立約戰按鈕

const formMessage = document.querySelector("#form-message"); //// 驗證未通過時的文字提示

// ====== 邀約表單中各欄位 ======
// 邀約標題
const battleTitleInput = document.querySelector("#battleTitle");

// 對戰模式
const battleModeSelect = document.querySelector("#battleMode");

// 玩家程度
const battleLevelSelect = document.querySelector("#battleLevel");

// 適合對象
const battleTargetSelect = document.querySelector("#battleTarget");

// 約戰日期
const battleDateInput = document.querySelector("#battleDate");

// 約戰時間
const battleTimeInput = document.querySelector("#battleTime");

// 報名截止時間
const battleDeadlineInput = document.querySelector("#battleDeadline");

// 詳細集合地點
const battleAddressInput = document.querySelector("#battleAddress");

// 聯絡資訊
const battleContactInput = document.querySelector("#battleContact");

// 邀約說明
const battleDescInput = document.querySelector("#battleDescription");


// ===== 預覽邀約卡上的各資料顯示欄位 =====
// 預覽卡封面
const previewCover = document.querySelector("#previewCover");

// 適合對象標籤
const previewTarget = document.querySelector("#previewTarget");

// 截止倒數
const previewCountdown = document.querySelector("#previewCountdown");

// 邀約標題
const previewTitle = document.querySelector("#previewTitle");

// 縣市
const previewCity = document.querySelector("#previewCity");

// 行政區
const previewDistrict = document.querySelector("#previewDistrict");

// 對戰模式
const previewMode = document.querySelector("#previewMode");

// 約戰日期與時間
const previewDate = document.querySelector("#previewDate");

// 玩家程度
const previewLevel = document.querySelector("#previewLevel");

// 邀約說明
const previewDesc = document.querySelector("#previewDescription");

//約戰日期的顯示格式轉換
function formatBattleDate(dateString) {
  const date = new Date(dateString);

  //之後利用getDay()可取得數字 0-6，代表星期日~星期一，可在透過這邊的陣列轉換回對應星期幾的文字
  const weekDays = [
    "日",
    "一",
    "二",
    "三",
    "四",
    "五",
    "六"
  ];

  const year = date.getFullYear();

  // 月份從0開始，所以需加 1，不足雙位數的月份前面補0
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");
  const weekDay = weekDays[date.getDay()];

  //回傳最後欲顯示的日期格式，e.g. 2026/08/15（六）14:00
  return `${year}/${month}/${day}（${weekDay}）${hours}:${minutes}`;
}


// 表單上的填寫內容整理為單筆完整邀約資料
function getBattleFormData() {

  // 將HTML上的約戰日期跟時間兩個欄位，合併為完整的單一日期時間資料
  const battleDateTime = `${battleDateInput.value}T${battleTimeInput.value}`;

  return {
    title: battleTitleInput.value.trim(),

    //提供後端資料庫存取的值
    mode: battleModeSelect.value,
    level: battleLevelSelect.value,
    target: battleTargetSelect.value,

    // 給前端預覽卡顯示時使用的文字
    modeText: battleModeSelect.selectedOptions[0].textContent,
    levelText: battleLevelSelect.selectedOptions[0].textContent,
    targetText: battleTargetSelect.selectedOptions[0].textContent,

    //提供後續傳回資料庫建立約戰資料時的縣市與行政區id
    cityId: Number(battleCitySelect.value),
    districtId: Number(battleDistrictSelect.value),

    // 供預覽卡畫面顯示上需要的縣市和行政區文字；selectedOption[0]可以用來抓取當前選單被選中的option
    city: battleCitySelect.selectedOptions[0].textContent,
    district: battleDistrictSelect.selectedOptions[0].textContent,

    battleDate: battleDateTime,
    deadline: battleDeadlineInput.value,
    address: battleAddressInput.value.trim(),
    contact: battleContactInput.value.trim(),
    description: battleDescInput.value.trim(),

    // 未上傳圖片時，使用平台預設圖
    coverImage:
      battleCoverInput.files[0] ? coverPreviewImage.src : "battle_card_default.jpg"
  };
}

/*
約戰時間與報名截止時間設定檢查
1. 約戰時間必須晚於當下時間
2. 報名截止時間必須晚於當下時間
3. 報名截止時間必須早於約戰時間
4. 報名截止時間至少要比當下晚1小時
*/

function validateBattleTime() {

  // 將約戰日期與時間組合後轉成 Date
  const battleDateTime = new Date(`${battleDateInput.value}T${battleTimeInput.value}`);

  // 將截止時間轉成 Date
  const deadline = new Date(battleDeadlineInput.value);

  // 取得填表單時的當下時間
  const now = new Date();

  // 約戰時間不能設定為過去的時間
  if (battleDateTime <= now) {
    alert("約戰日期與時間必須晚於目前時間");
    battleDateInput.focus();
    return false;
  }

  // 截止時間不能設定為過去的時間
  if (deadline <= now) {
    alert("報名截止時間必須晚於目前時間");
    battleDeadlineInput.focus();
    return false;
  }

  // 先抓一小時後的時間
  const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);

  // 設定截止時間至少需保留一小時
  if (deadline < oneHourLater) {
    alert("報名截止時間至少需晚於目前時間一小時");
    battleDeadlineInput.focus();
    return false;
  }

  // 截止時間不能晚於或等於約戰時間
  if (deadline >= battleDateTime) {
    alert("報名截止時間必須早於約戰時間");
    battleDeadlineInput.focus();
    return false;
  }

  // 所有時間的設定條件都通過時才return true，通過驗證
  return true;
}


// 約戰預覽卡倒數功能設定

let previewDeadline = ""; // 紀錄目前預覽卡被設定的截止時間
let previewCountdownTimer = null; // 倒數計時器

//更新倒數函式
function updatePreviewCountdown() {

  // 如果還沒設定報名截止時間，就停止往下跑
  if (!previewDeadline) {
    return;
  }

  // 把截止時間轉成毫秒
  const deadline = new Date(previewDeadline).getTime();

  // 取得當前時間
  const now = Date.now();

  // 計算距離截止日期的剩餘毫秒
  const remainingTime = deadline - now;

  if (remainingTime <= 0) {
    previewCountdown.textContent = "報名已截止";
    return;
  }

  // 把剩餘時間換算為總秒數後，再計算總共剩餘多少天、小時、分鐘
  const totalSeconds = Math.floor(remainingTime / 1000);
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  // 剩餘時間大於一天時，不顯示秒數
  if (days > 0) {
    previewCountdown.textContent = `${days}天 ${hours}時 ${minutes}分`;

  } else {
    previewCountdown.textContent = `${hours}時 ${minutes}分 ${seconds}秒`;
  }
}

// 預覽發起人頭像
const previewHostAvatar = document.querySelector("#previewHostAvatar");
// 預覽發起人名稱
const previewHostName = document.querySelector("#previewHostName");

// 會員頭像路徑判斷函式
function getMemberAvatarUrl(photo) {

  // 沒有頭像資料時，使用平台預設頭像
  if (!photo) {
    return (
      import.meta.env.BASE_URL +
      "spinix_member_default.png"
    );
  }

  // 會員自己動態上傳的頭像
  if (photo.startsWith("uploads/member/")) {
    return `${phpBaseUrl}/${photo}`;
  }

  // 原本放在 public 裡的靜態會員頭像
  return import.meta.env.BASE_URL + photo;
}

// 將使用者填寫好的邀約資料放進預覽卡
function renderBattlePreview(battle) {

  //發起人姓名與頭像
  if (currentMember) {
    previewHostAvatar.src = getMemberAvatarUrl(currentMember.photo);

    previewHostAvatar.alt = `${currentMember.name}的會員頭像`;

    previewHostName.textContent = currentMember.name;
  }

  //封面圖設定
  previewCover.src = battle.coverImage;
  previewCover.alt = `${battle.title}的邀約封面`;

  //適合對象標籤
  previewTarget.textContent = battle.targetText;

  //邀約標題
  previewTitle.textContent = battle.title;

  //縣市與行政區
  previewCity.textContent = battle.city;
  previewDistrict.textContent = battle.district;

  //對戰模式
  previewMode.textContent = battle.modeText;

  //玩家程度
  previewLevel.textContent = battle.levelText;

  //邀約說明
  previewDesc.textContent = battle.description;

  // ===== 約戰日期與時間 =====
  previewDate.dateTime = battle.battleDate;

  // 將日期轉換為卡片最終要顯示的格式
  previewDate.textContent = formatBattleDate(battle.battleDate);

  //報名截止倒數設定
  //將使用者設定的截止日期存入全域變數
  previewDeadline = battle.deadline;

  // 如果之前已經建立過倒數計時器，先停止舊的
  if (previewCountdownTimer !== null) {
    clearInterval(previewCountdownTimer);
  }

  // 執行報名截止時間倒數函式
  updatePreviewCountdown();

  // 建立新的計時器，每秒更新一次倒數
  previewCountdownTimer = setInterval(updatePreviewCountdown, 1000);
}

// 點擊「預覽邀約」按鈕設定
previewBtn.addEventListener("click", function () {

  // 每次檢查前，先隱藏之前的錯誤提示
  formMessage.hidden = true;

  // 1. 檢查required欄位
  const isFormValid = battleForm.checkValidity();

  // 當有必填欄位未完成，顯示系統提示文自
  if (!isFormValid) {

    formMessage.hidden = false;

    // 顯示瀏覽器原生的欄位錯誤提示
    battleForm.reportValidity();

    // 停止後續預覽流程
    return;
  }

  // 2.檢查約戰時間與截止時間
  const isTimeValid = validateBattleTime();

  if (!isTimeValid) {
    return;
  }

  // 3. 整理表單資料
  // 執行前面寫好的函式，將所有欄位整理成一個邀約物件
  const newBattle = getBattleFormData();

  // 4. 更新預覽卡
  renderBattlePreview(newBattle);

  // 5. 切換畫面
  // 隱藏原先的填寫表單區
  formView.hidden = true;

  // 接著顯示預覽區
  previewView.hidden = false;

  // 平滑移動到預覽區上方
  previewView.scrollIntoView({
    behavior: "smooth",
    block: "start"
  });
});

// 點擊「返回修改」按鈕
returnEditBtn.addEventListener("click", function () {

  // 若目前有倒數計時器，就停止
  if (previewCountdownTimer !== null) {
    clearInterval(previewCountdownTimer);

    // 恢復成沒有計時器的狀態
    previewCountdownTimer = null;
  }

  // 隱藏預覽區
  previewView.hidden = true;

  // 重新顯示表單區
  formView.hidden = false;

  // 捲動回表單區上方
  formView.scrollIntoView({
    behavior: "smooth",
    block: "start"
  });
});


//表單區左側進度瀏覽效果設定
const formProgressSticky = document.querySelector(".form-progress-sticky"); //表單左側的整個進度條
const progressSpinnerImage = document.querySelector(".progress-spinner img"); //進度轉動陀螺圖
const progressValue = document.querySelector(".progress-value"); //進度文字
const requiredFields = document.querySelectorAll('[required]'); //表單所有必填欄位

// 根據目前捲動位置，更新陀螺旋轉角度
function updateFormScrollProgress() {

  // 取得表單於整個網頁中的起始位置
  // getBoundingClientRect可以用來抓取指定元素相對於目前瀏覽器畫面的位置
  const formStart = battleForm.getBoundingClientRect().top + window.scrollY - 120;
  // -120 → 當表單距離畫面頂端還有 120px 時，就把它視為進度開始，120px也是陀螺設定sticky的位置

  // 當表單底部接近畫面底部，再往下捲120px後，視為瀏覽完成
  const formEnd = battleForm.getBoundingClientRect().bottom + window.scrollY - window.innerHeight + 120;

  // 瀏覽表單時的總捲動距離
  const scrollRange = formEnd - formStart;

  // 取得目前已在表單範圍內捲動多少距離
  const currentScroll = window.scrollY - formStart;

  // 將目前瀏覽進度換算成 0～1 → 之後做百分比切換
  let progress = currentScroll / scrollRange;

  // 限制進度最低不能小於0，最大不能超過1
  progress = Math.min(Math.max(progress, 0), 1);

  // 設定進度從 0～100% 時，陀螺總共會旋轉兩圈
  const rotation = progress * 720;

  progressSpinnerImage.style.transform = `rotate(${rotation}deg)`;
}

// 當使用者每次捲動畫面時，都重新計算表單瀏覽捲動進度
window.addEventListener("scroll", updateFormScrollProgress);

// 瀏覽器尺寸改變時，也重新計算
window.addEventListener("resize", updateFormScrollProgress);

// 頁面剛載入時，先設定一次初始狀態
updateFormScrollProgress();

// 根據使用者表單填寫狀況，更新右側的進度數字顯示
function updateFormProgress() {
  let completedCount = 0; //紀錄已填寫的必填欄位數

  requiredFields.forEach(field => {
    //去除文字輸入欄位的前後空白，避免誤觸空白時也被視為已填寫
    const value = field.value.trim();

    //當該欄位非空值時，已填寫欄位數 + 1
    if (value != "") {
      completedCount++;
    }
  });

  //計算填寫進度的百分比 → 已填寫欄數 除以 總必填欄數
  const progressPercent = Math.round(completedCount / requiredFields.length * 100);

  //更新顯示數字
  progressValue.textContent = `${progressPercent}%`;

  // 當必填欄位都填寫完時 (進度100%)，加入 class 樣式 is-complete
  if (progressPercent === 100) {
    formProgressSticky.classList.add("is-complete");
  } else {
    formProgressSticky.classList.remove('is-complete')
  }
}

// 監聽change事件，欄位選項輸入完畢時，更新進度
battleForm.addEventListener("change", updateFormProgress);
// 載入頁面時，更新初始文字進度
updateFormProgress();



// 點擊「確認建立邀約」按鈕
confirmCreateBtn.addEventListener("click", function () {
  const isConfirmed = confirm(
    "確定要建立這筆對戰邀約嗎？"
  );

  if (!isConfirmed) {
    return;
  }

  //避免會員連續點擊建立約戰
  confirmCreateBtn.disabled = true;
  confirmCreateBtn.textContent = "建立中...";

  //準備串接後端建立約戰api
  //先取得準備建立約戰的相關彙整資料
  const battle = getBattleFormData();

  //將約戰資料打包進formData中，再傳給後端API
  const formData = new FormData();
  formData.append("title", battle.title); //標題
  formData.append("mode", battle.mode); //模式
  formData.append("level", battle.level); //程度
  formData.append("target", battle.target); //對象

  formData.append("city_id", battle.cityId); //城市ID
  formData.append("district_id", battle.districtId); //行政區ID

  formData.append("battle_date", battle.battleDate); //約戰日期
  formData.append("deadline", battle.deadline); //截止日期

  formData.append("address", battle.address); //詳細地點
  formData.append("contact", battle.contact); //聯絡方式
  formData.append("description", battle.description); //約戰說明

  //當會員有上傳封面圖時，將圖片檔案放進formData
  if (battleCoverInput.files[0]) {
    formData.append(
      "cover_image",
      battleCoverInput.files[0]
    );
  }

  fetch(`${phpBaseUrl}/battle/battle_create_post.php`, {
    method: "POST",
    body: formData,
    credentials: "include"
  }).then(res => res.json()).then(data => {

    // 當後端回傳失敗時，不跳頁
    if (!data.success) {
      alert(data.message);

      //等API完成回傳後，將按鈕恢復原本正常樣式
      confirmCreateBtn.disabled = false;
      confirmCreateBtn.textContent = "確認送出";

      return;
    }

    // 建立成功後，停止預覽卡倒數計時器
    if (previewCountdownTimer !== null) {
      clearInterval(previewCountdownTimer);
      previewCountdownTimer = null;
    }

    alert(data.message);

    // 回到對戰配對列表
    window.location.href = "battle.html";

  }).catch(error => {
    console.error("建立約戰失敗：", error);

    alert("系統發生錯誤，請稍後再試");

    confirmCreateBtn.disabled = false;
    confirmCreateBtn.textContent = "確認送出";
  });

});