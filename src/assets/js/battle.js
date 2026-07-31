import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";

createApp(Header).mount("#headerApp");
createApp(Footer).mount("#footerApp");

//Hero區視差設定
const battleHeroScroll = document.querySelector(".battle-hero-scroll");
const battleHero = document.querySelector(".battle-sec-hero");
const heroSpintop = document.querySelector(".hero-spintop img");

// 讓這個頁面可以使用GSAP的ScrollTrigger外掛
gsap.registerPlugin(ScrollTrigger);

//更新 ScrollTrigger 的尺寸與捲動進度
function refreshHeroScroll() {
    // 等瀏覽器完成這一幀的版面計算後再 refresh
    requestAnimationFrame(() => {
        ScrollTrigger.refresh();
        ScrollTrigger.update();
    });
}

/*
    建立一條時間軸。

    ScrollTrigger 綁在整條時間軸上，
    所以裡面的背景動畫與陀螺動畫會共用同一段捲動進度。
*/
let heroTimeline;

// 建立Hero區的ScrollTrigger動畫
function createHeroAnimation() {

    //如果先前已經建立過動畫，重新建立前要先清除舊動畫
    if (heroTimeline) {
        heroTimeline.scrollTrigger?.kill();
        heroTimeline.kill();
    }

    heroTimeline = gsap.timeline({
        scrollTrigger: {
            // 將外層容器作為動畫範圍
            trigger: battleHeroScroll,

            // 動畫自外層頂端碰到視窗頂端時開始
            start: "top top",

            // 外層底部碰到視窗底部時結束
            end: "bottom bottom",

            // 動畫進度跟著捲軸，1 代表有些微平滑追蹤
            scrub: 1,

            invalidateOnRefresh: true,
            markers: false //設定不出現start / end的測試字樣
        }
    });

    //第一段動畫，hero區的底圖顯示從較上方位置，移動到下方
    heroTimeline.fromTo(
        battleHero,
        {
            backgroundPosition: "center 15%"
        },
        {
            backgroundPosition: "center 100%",
            ease: "none"
        },
        0
    );

    //第二段動畫，陀螺圖同步跟著漸入，並順時針旋轉一圈
    heroTimeline.fromTo(
        heroSpintop,
        {
            opacity: 0.1,
            rotation: 0
        },
        {
            opacity: 1,
            rotation: 360,
            ease: "none"
        },
        0
    );
}

//頁面剛進入時，先建立一次Hero動畫
createHeroAnimation();


// 所有圖片與資源完成載入時重新同步
window.addEventListener("load", () => {
    refreshHeroScroll();
});

// 瀏覽器上一頁、下一頁或快取恢復頁面時
window.addEventListener("pageshow", () => {
    refreshHeroScroll();
});

//篩選列表上方的介紹文字動畫設定
const tagIntro = document.querySelector(".tag-intro"); //介紹文字區(文+陀螺圖)
const tagIntroSpintop = document.querySelector(".tag-intro-spintop"); //陀螺圖
const tagIntroText = document.querySelector(".tag-intro p"); //介紹文字

gsap.timeline({
    scrollTrigger: {
        //當介紹文字區進入瀏覽畫面時啟動動畫
        trigger: tagIntro,

        //當介紹區的頂端進到視窗約85%的位置時觸發
        start: "top 85%",

        //只播放一次，不因為上下捲動而重複播放
        once: true,
        markers: false
    }
}).fromTo(
    //小陀螺動畫
    tagIntroSpintop,
    {
        x: -80, //設定陀螺從左側位置進場 -70
        opacity: 0, //起始時透明
        rotation: -540, // -360
        //起始先讓陀螺逆時針轉動一圈，後續再設定rotate 0，製造陀螺向右順時鐘滾動一圈的效果
        visibility: "visible"
    },
    {
        x: 0, //陀螺回到原本位置
        opacity: 1, //完全顯示
        rotation: 0, //順時針轉回至正常角度
        duration: 1.2, //動畫時間 0.9
        ease: "power3.out" //靠近終點時稍微減速
    }
).fromTo(
    //文字動畫
    tagIntroText,
    {
        y: 12, //讓文字以由下至上的方式進場
        opacity: 0,
        visibility: "visible"
    },
    {
        y: 0,
        opacity: 1,
        duration: 0.55,
        ease: "power2.out"
    },

    //指這段動畫會在0.45秒時才開始動作，所以會在上一個陀螺圖移動到一半時，文字動畫就會開始啟動 → 當陀螺動畫播放到約0.45秒時，就讓文字開始出現
    0.45
);


//建立縣市與行政區資料
const cityDistricts = {
    "臺北市": [
        "中正區",
        "大同區",
        "中山區",
        "松山區",
        "大安區",
        "信義區"
    ],

    "新北市": [
        "板橋區",
        "中和區",
        "永和區",
        "新莊區"
    ],

    "桃園市": [
        "桃園區",
        "中壢區",
        "平鎮區",
        "八德區"
    ]
};

const citySelect = document.querySelector("#citySelect"); //城市選擇欄位
const districtSelect = document.querySelector("#districtSelect"); //行政區選擇欄位

//帶入各個城市與行政區至option中
function initSelect() {
    // Object.keys(...) → 將目標物件中的key值(即城市名)提出，單獨拉出，建立一個陣列
    Object.keys(cityDistricts).forEach(city => {
        const option = document.createElement("option"); //動態建立html的option標籤
        option.value = city; //帶入陣列中的每個城市做為option的值
        option.textContent = city; //設定各option的顯示字樣為城市名稱
        citySelect.append(option); //將新生成的option加入至select內
    });
}

initSelect(); //一進頁面時，就先將select的城市選項準備好


//行政區顯示設定
citySelect.addEventListener("change", function () {
    districtSelect.innerHTML =
        `<option value="">全部行政區</option>`;

    //若還未選縣市時，行政區disabled不能選
    if (!this.value) {
        districtSelect.disabled = true;
        return;
    }

    districtSelect.disabled = false;

    //將被選中城市的行政區跑forEach，放進行政區欄位中
    cityDistricts[this.value].forEach(district => {
        const option = document.createElement("option");
        option.value = district;
        option.textContent = district;
        districtSelect.append(option);
    });

});

//篩選重置
const resetBtn = document.querySelector("#resetBtn");

resetBtn.addEventListener("click", () => {

    battleType.value = "";

    battleTarget.value = "";

    playerLevel.value = "";

    citySelect.value = "";

    districtSelect.innerHTML =
        `<option value="">全部行政區</option>`;

    districtSelect.disabled = true;

    startDate.value = "";

    endDate.value = "";

    locationStatus.textContent = "";

    //將篩選條件都初始化後，再重新執行render卡片
    battleFilter();
});

//自動定位設定
const locateBtn = document.querySelector("#locateBtn"); //自動定位按鈕

const locationLoading = document.querySelector("#locationLoading"); //等待動畫
const locationLoadingText = document.querySelector("#locationLoadingText"); // 載入動畫旁邊的提示文字

const locationStatus = document.querySelector("#locationStatus"); //系統提示文字
//記錄目前是否正在執行自動定位
let isLocating = false;
//記錄定位期間，頁面是否因瀏覽器權限詢問視窗而失去焦點
let locationWindowInterrupted = false;

// 定位期間，顯示等待動畫
function loadingAnime(message) {
    // 隱藏定位按鈕
    locateBtn.style.display = "none";

    // 顯示載入動畫
    locationLoading.classList.add("is-loading");

    // 更新提示文字
    locationLoadingText.textContent = message;

    // 清除前一次定位留下的結果文字
    locationStatus.textContent = "";
}

// 結束定位，隱藏動畫
function hideLoading() {
    // 重新顯示定位按鈕
    locateBtn.style.display = "";

    // 隱藏載入動畫
    locationLoading.classList.remove("is-loading");

    //目前的定位流程已經結束
    isLocating = false;

    //如果定位期間曾出現權限詢問視窗，就重新建立Hero動畫
    if (locationWindowInterrupted) {
        setTimeout(() => {
            createHeroAnimation();
            ScrollTrigger.refresh();
            ScrollTrigger.update();
        }, 100);
    }

    //處理完成後，將紀錄恢復成預設狀態
    locationWindowInterrupted = false;
}

//自動定位功能設定
locateBtn.addEventListener("click", () => {

    //先確認當前瀏覽器是不是支援定位功能
    if (!navigator.geolocation) {
        locationStatus.textContent = "目前瀏覽器不支援定位功能";
        return;
    }

    //開始執行定位
    isLocating = true;

    //每次重新定位前，先清除前一次的中斷紀錄
    locationWindowInterrupted = false;

    loadingAnime('正在取得你的位置');

    // getCurrentPosition()，取得使用者的目前位置，第一個參數設定定位成功時執行的函式；第二個參數是失敗時的函式；第三個參數是定位相關設定
    navigator.geolocation.getCurrentPosition(
        locationSuccess,
        locationError,
        {
            // 優先取得較精確的位置
            enableHighAccuracy: true,

            // 最多等待 10 秒
            timeout: 10000,

            // 避免抓到之前的舊位置，每次都得重新定位
            maximumAge: 0
        }
    );
});

//定位期間若瀏覽器跳出權限詢問視窗，頁面會暫時失去焦點
window.addEventListener("blur", () => {
    if (isLocating) {
        locationWindowInterrupted = true;
    }
});

async function locationSuccess(position) {

    //取得座標經緯度
    const latitude = position.coords.latitude;
    const longitude = position.coords.longitude;

    try {
        //使用反向地理編碼 API，把經緯度轉換成縣市與行政區。
        const apiUrl =
            "https://nominatim.openstreetmap.org/reverse" +
            "?format=jsonv2" +
            `&lat=${latitude}` +
            `&lon=${longitude}` +
            "&accept-language=zh-TW";

        const response = await fetch(apiUrl);

        // API 回應異常時，主動進入 catch
        if (!response.ok) {
            throw new Error("地址資料查詢失敗");
        }

        // 將 API 回傳的 JSON 資料轉成 JS 物件
        const data = await response.json();

        // 地址相關資訊會放在 address 裡
        const address = data.address;

        //不同地區的 API 回傳欄位可能不完全相同，因此要依序嘗試從可能的欄位取得縣市和行政區資料。 
        let detectedCity =
            address.city ||
            address.county ||
            address.state ||
            "";

        let detectedDistrict =
            address.city_district ||
            address.town ||
            address.suburb ||
            address.district ||
            "";

        //API有時可能回傳「台北市」，但因為網頁上的資料已經使用「臺北市」，因此統一轉成「臺」
        detectedCity =
            detectedCity.replace("台", "臺");

        detectedDistrict =
            detectedDistrict.replace("台", "臺");

        //定位出的縣市如果不在目前網站設定的資料中時，顯示對應系統提示文
        if (!cityDistricts[detectedCity]) {
            locationStatus.textContent = "無法判斷目前所在位置，請手動選擇";

            return;
        }

        // 自動將縣市 select 切換到定位結果
        citySelect.value = detectedCity;

        //因應縣市選擇設定後，行政區的選項也要先重新更新
        districtSelect.innerHTML =
            `<option value="">全部行政區</option>`;

        //重新將縣市對應的行政區選項render出
        cityDistricts[detectedCity].forEach(district => {
            const option = document.createElement("option");
            option.value = district;
            option.textContent = district;
            districtSelect.append(option);
        });

        // 有設定縣市後，行政區欄位要關閉disabled設定
        districtSelect.disabled = false;

        //確認定位到的行政區是否有存在目前網頁資料中
        const hasDistrict = cityDistricts[detectedCity].includes(detectedDistrict);

        if (hasDistrict) {
            // 找到對應行政區時，自動選取
            districtSelect.value = detectedDistrict;
        } else {
            //否則顯示「全部行政區」
            districtSelect.value = "";
        }

        // 顯示定位成功結果
        locationStatus.textContent = hasDistrict
            ? `已定位至 ${detectedCity}${detectedDistrict}`
            : `已定位至 ${detectedCity}`;

        //帶入定位位置後，重新render約戰卡
        battleFilter();

    } catch (error) {
        // 在開發者工具中保留錯誤資訊，方便除錯
        console.error("反向地理編碼失敗：", error);

        locationStatus.textContent =
            "無法取得所在行政區，請手動選擇";

    } finally {
        //無論最後定位是否成功，都要關閉載入動畫
        hideLoading();
    }
}

function locationError(error) {

    //error.code會提供定位失敗的原因，可根據不同原因，調整欲顯示的系統文字
    switch (error.code) {
        case error.PERMISSION_DENIED:
            // 使用者拒絕提供定位權限
            locationStatus.textContent =
                "定位權限未開啟";
            break;

        case error.POSITION_UNAVAILABLE:
            // 裝置或瀏覽器目前無法取得位置
            locationStatus.textContent =
                "無法取得你的位置";
            break;

        case error.TIMEOUT:
            // 超過前面設定的 10 秒等待時間
            locationStatus.textContent =
                "定位時間過久，請稍後再試";
            break;

        default:
            locationStatus.textContent =
                "定位失敗，請手動選擇地區";
    }

    //失敗時也要記得關閉等待動畫
    hideLoading();
}

//針對發起人的評價假資料
const hostReviewData = {
    //發起人約戰歷史評分紀錄
    101: {
        hostId: 101,
        name: "WeiChen",
        avatar: "/spinix_member_default.png",
        totalBattles: 18,
        averageRating: 4.7,
        //過往評論紀錄
        reviews: [
            {
                reviewId: 1001,
                reviewerName: "小宇",
                rating: 5,
                content: "發起人很準時，也很親切，現場交流氣氛很好！",
                createdAt: "2026-07-15"
            },
            {
                reviewId: 1002,
                reviewerName: "BladeKen",
                rating: 5,
                content: "場地資訊說明得很清楚，對新手也很有耐心。",
                createdAt: "2026-07-08"
            },
            {
                reviewId: 1003,
                reviewerName: "阿哲",
                rating: 4,
                content: "整體約戰體驗很好，下次有機會還會再參加。",
                createdAt: "2026-06-29"
            },
            {
                reviewId: 1004,
                reviewerName: "Ray",
                rating: 5,
                content: "準備了戰鬥盤，時間安排也很順利。",
                createdAt: "2026-06-17"
            },
            {
                reviewId: 1005,
                reviewerName: "陀螺新手",
                rating: 4,
                content: "交流過程很友善，適合剛接觸戰鬥陀螺的玩家。",
                createdAt: "2026-06-03"
            }
        ]
    },

    102: {
        hostId: 102,
        name: "SpinRay",
        avatar: "/spinix_member_default.png",
        totalBattles: 18,
        averageRating: 4.7,
        reviews: [
            {
                reviewId: 2001,
                reviewerName: "小宇",
                rating: 5,
                content: "發起人很準時，也很親切，現場交流氣氛很好！",
                createdAt: "2026-07-15"
            },
            {
                reviewId: 2002,
                reviewerName: "BladeKen",
                rating: 5,
                content: "場地資訊說明得很清楚，對新手也很有耐心。",
                createdAt: "2026-07-08"
            },
            {
                reviewId: 2003,
                reviewerName: "阿哲",
                rating: 4,
                content: "整體約戰體驗很好，下次有機會還會再參加。",
                createdAt: "2026-06-29"
            },
            {
                reviewId: 2004,
                reviewerName: "Ray",
                rating: 5,
                content: "準備了戰鬥盤，時間安排也很順利。",
                createdAt: "2026-06-17"
            },
            {
                reviewId: 2005,
                reviewerName: "陀螺新手",
                rating: 4,
                content: "交流過程很友善，適合剛接觸戰鬥陀螺的玩家。",
                createdAt: "2026-06-03"
            }
        ]
    }
};

// 約戰邀約卡片假資料
const battleData = [
    {
        battleId: 1,
        title: "新手友善！開心打陀螺",
        coverImage: "/battle_card_default.jpg",
        target: "不限對象",
        city: "桃園市",
        district: "中壢區",
        mode: "休閒模式",
        battleDate: "2026-08-15T14:00:00",
        deadline: "2026-08-21T22:00:00",
        level: "新手玩家",
        description: "假日放鬆場，輕鬆交流，我帶戰鬥盤，你帶陀螺就好！",
        hostId: 101,
        status: "matching"
    },
    {
        battleId: 2,
        title: "中壢車站週末交流場",
        coverImage: "/battle_card_test1.jpg",
        target: "不限對象",
        city: "桃園市",
        district: "中壢區",
        mode: "休閒模式",
        battleDate: "2026-08-16T15:00:00",
        deadline: "2026-08-15T20:00:00",
        level: "不限程度",
        description: "歡迎各種程度的玩家參加，現場會準備戰鬥盤。",
        hostId: 102,
        status: "matching"
    },
    {
        battleId: 3,
        title: "競技模式實戰練習",
        coverImage: "/battle_card_default.jpg",
        target: "成人限定",
        city: "臺北市",
        district: "大安區",
        mode: "競技模式",
        battleDate: "2026-08-17T19:00:00",
        deadline: "2026-08-16T18:00:00",
        level: "進階玩家",
        description: "以競技規則進行實戰交流，適合已有對戰經驗的玩家。",
        hostId: 101,
        status: "matching"
    },
    {
        battleId: 4,
        title: "親子陀螺交流體驗",
        coverImage: "/battle_card_default.jpg",
        target: "親子友善",
        city: "新北市",
        district: "板橋區",
        mode: "休閒模式",
        battleDate: "2026-08-22T10:30:00",
        deadline: "2026-08-21T18:00:00",
        level: "新手玩家",
        description: "適合親子一起參加的輕鬆交流場，歡迎第一次接觸的玩家。",
        hostId: 102,
        status: "matching"
    },
    {
        battleId: 5,
        title: "下班後來一場吧！",
        coverImage: "/battle_card_default.jpg",
        target: "成人限定",
        city: "臺北市",
        district: "信義區",
        mode: "休閒模式",
        battleDate: "2026-08-20T19:30:00",
        deadline: "2026-08-20T12:00:00",
        level: "中階玩家",
        description: "下班後簡單玩幾場，地點鄰近捷運站，交通方便。",
        hostId: 101,
        status: "matching"
    },
    {
        battleId: 6,
        title: "進階玩家配置測試場",
        coverImage: "/battle_card_test2.jpg",
        target: "不限對象",
        city: "新北市",
        district: "新莊區",
        mode: "競技模式",
        battleDate: "2026-08-23T14:00:00",
        deadline: "2026-08-22T22:00:00",
        level: "進階玩家",
        description: "帶上最近調整的配置，一起測試不同零件搭配的實戰效果。",
        hostId: 102,
        status: "matching"
    },
    {
        battleId: 7,
        title: "初次約戰也不用緊張",
        coverImage: "/battle_card_default.jpg",
        target: "不限對象",
        city: "桃園市",
        district: "八德區",
        mode: "休閒模式",
        battleDate: "2026-08-29T13:00:00",
        deadline: "2026-08-28T20:00:00",
        level: "新手玩家",
        description: "以交流和認識同好為主，不熟悉規則也可以放心參加。",
        hostId: 101,
        status: "matching"
    },
    {
        battleId: 8,
        title: "週日下午競技交流",
        coverImage: "/battle_card_default.jpg",
        target: "成人限定",
        city: "桃園市",
        district: "平鎮區",
        mode: "競技模式",
        battleDate: "2026-08-30T15:00:00",
        deadline: "2026-08-29T23:00:00",
        level: "中階玩家",
        description: "依照競技規則進行多場對戰，歡迎想累積實戰經驗的玩家。",
        hostId: 102,
        status: "matching"
    },
    {
        battleId: 9,
        title: "桃園陀螺玩家輕鬆聚",
        coverImage: "/battle_card_default.jpg",
        target: "親子友善",
        city: "桃園市",
        district: "桃園區",
        mode: "休閒模式",
        battleDate: "2026-09-05T14:30:00",
        deadline: "2026-09-04T21:00:00",
        level: "不限程度",
        description: "不論是收藏、配置分享或實際對戰都歡迎，一起認識附近同好。",
        hostId: 101,
        status: "matching"
    }
];

//轉換日期時間
function formatBattleDate(dateString) {
    const date = new Date(dateString);

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
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    const weekDay = weekDays[date.getDay()];

    return `${year}/${month}/${day}（${weekDay}）${hours}:${minutes}`;
}

//生成對戰邀約卡
function createBattleCard(battle) {
    const host = hostReviewData[battle.hostId];

    //邀約卡片HTML架構生成
    return `
    <article class="battleCard" data-battle-id="${battle.battleId}">
      <div class="pic">
        <img
          class="battle-cover"
          src="${battle.coverImage}"
          alt="${battle.title}的邀約封面"
        >
        <span class="target-tag">${battle.target}</span>
      </div>

      <div class="items">
        <div class="timer">
          <i class="fa-regular fa-clock"></i>
          <p>
            截止倒數：
            <span
              class="countdown"
              data-deadline="${battle.deadline}"
            >
              計算中...
            </span>
          </p>
        </div>

        <div class="info">
          <h4 class="battle-title">${battle.title}</h4>

          <div class="battle-meta">
            <div class="meta-item battle-location">
              <i class="fa-solid fa-location-dot"></i>
              <p>
                <span class="battle-city">${battle.city}</span>
                <span>/</span>
                <span class="battle-district">${battle.district}</span>
              </p>
            </div>

            <div class="meta-item battle-mode">
              <i class="fa-solid fa-trophy"></i>
              <p>${battle.mode}</p>
            </div>

            <div class="meta-item battle-date">
              <i class="fa-solid fa-calendar-days"></i>
              <time datetime="${battle.battleDate}">
                ${formatBattleDate(battle.battleDate)}
              </time>
            </div>

            <div class="meta-item battle-level">
              <i class="fa-solid fa-signal"></i>
              <p>${battle.level}</p>
            </div>
          </div>

          <p class="battle-desc">${battle.description}</p>
        </div>

        <div class="card-footer">
          <button
            type="button"
            class="hostBtn"
            data-host-id="${host.hostId}"
          >
            <img
              class="host-avatar"
              src="${host.avatar}"
              alt=""
            >
            <span class="host-name">${host.name}</span>
          </button>

          <button
            type="button"
            class="btnFill applyBtn"
            data-battle-id="${battle.battleId}"
          >
            申請加入
          </button>
        </div>
      </div>
    </article>
  `;
}

const battleCardList = document.querySelector("#battleCardList"); //擺放邀約卡片的列表容器
const moreBtn = document.querySelector("#moreBtn"); //載入更多按鈕

const cardsPerLoad = 6; //每次顯示6張卡片
let visibleCardCount = cardsPerLoad; // 初始畫面的卡片顯示數量定為6張
let currentBattleList = []; // 記錄目前經過篩選後的卡片資料

//render出每張邀約卡片
function renderBattleCards(battles) {

    // 保存目前經過分類條件(e.g.狀態、期限)篩選後的資料
    currentBattleList = battles;

    // 從第0筆開始，只取出目前允許顯示的數量
    const visibleBattles = currentBattleList.slice(0, visibleCardCount);

    //當沒有符合條件的對戰邀約時，需要有對應的系統文字提示!!!
    if (battles.length === 0) {
        battleCardList.innerHTML = `
      <p class="battle-empty">
        目前沒有符合條件的對戰邀約
      </p>
    `;

        moreBtn.style.display = "none";

        return;
    }

    battleCardList.innerHTML = visibleBattles.map(createBattleCard).join("");

    // 若目前卡片顯示數量小於所有符合條件的卡片數量，代表還有卡片還沒被render，所以載入按鈕保持block；否則就隱藏載入按鈕
    if (visibleCardCount < currentBattleList.length) {
        moreBtn.style.display = "block";
    } else {
        moreBtn.style.display = "none";
    }
}

// 「載入更多」按鈕設定
moreBtn.addEventListener("click", () => {

    // 每點一次，可顯示的卡片數量就加6
    visibleCardCount += cardsPerLoad;

    // 使用目前的篩選結果重新render約戰卡片
    renderBattleCards(currentBattleList);

    //因重新render後產生了新的倒數DOM，所以要立即更新一次倒數內容。
    updateAllCountdowns();
});

// 生成畫面中的卡片前，先篩選掉截止時間已到期，或是已有人申請加入(即狀態為pending)的卡片
const availableBattles = battleData.filter(battle => {
    //條件一：確認邀約卡的申請時間還未過期
    const isNotExpired = new Date(battle.deadline).getTime() > Date.now();
    //條件二：確保邀約卡的狀態仍為matching配對中
    const isMatching = battle.status === "matching";

    return isNotExpired && isMatching;
});

//篩選完卡片後，再執行render邀約卡的函式
renderBattleCards(availableBattles);

// 卡片生成後，進行倒數計時
function updateCountdown(element) {

    //計算截止日期跟當下時間的時間差
    const deadline = new Date(element.dataset.deadline).getTime();
    const now = Date.now();
    const remainingTime = deadline - now;

    if (remainingTime <= 0) {
        // 將倒數時間改為「報名已截止」
        element.textContent = "報名已截止";

        // 從目前的倒數元素往外找，找到其所屬的邀約卡
        const battleCard = element.closest(".battleCard");

        if (battleCard) {
            // 將該張卡片加上已到期的class名稱
            battleCard.classList.add("is-expired");

            // 鎖定該張卡片的申請加入按鈕，將其狀態改為disabled，不得加入
            const applyBtn = battleCard.querySelector(".applyBtn");

            if (applyBtn) {
                // disabled 會讓按鈕無法再被點擊
                applyBtn.disabled = true;

                // 將按鈕文字由「申請加入」改成「已截止」
                applyBtn.textContent = "已截止";
            }
        }

        return;
    }

    const totalSeconds = Math.floor(remainingTime / 1000);

    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (days > 0) {
        element.textContent =
            `${days}天 ${hours}時 ${minutes}分`;
    } else {
        element.textContent =
            `${hours}時 ${minutes}分 ${seconds}秒`;
    }
}

//抓取HTML中所有的倒數計時 class=countdown的元素，持續更新倒數時間
function updateAllCountdowns() {
    const countdownElements = document.querySelectorAll(".countdown");

    countdownElements.forEach(updateCountdown);
}

updateAllCountdowns();

// 每秒執行一次，製造倒數效果
const countdownTimer = setInterval(
    updateAllCountdowns,
    1000
);

//約戰歷史紀錄燈箱設定
const historyModal = document.querySelector("#historyModal");
//燈箱中發起人頭像
const historyAvatar = document.querySelector("#historyAvatar");
//燈箱發起人姓名slot
const historyHostName = document.querySelector("#history-hostName");
const totalBattles = document.querySelector("#totalBattles"); //總約戰場次slot
const averageRating = document.querySelector("#averageRating");//平均評分slot
const reviewList = document.querySelector("#reviewList");//評論列表

// render出個別評論函式
function createReviewItem(review) {
    return `
        <article class="review-item">
            <div class="review-info">
                <span class="reviewer-name">
                    ${review.reviewerName}
                </span>

                <span class="review-rating">
                    ${review.rating}
                    <span class="rating-star">★</span>
                </span>
            </div>

            <p class="review-content">
                ${review.content}
            </p>

            <time class="review-date">
                ${review.createdAt}
            </time>
        </article>
    `;
}

// 將發起人資料放入燈箱中
function renderHostHistory(host) {
    historyAvatar.src = host.avatar;
    historyAvatar.alt = `${host.name}的會員頭像`;

    historyHostName.textContent = host.name;
    totalBattles.textContent = host.totalBattles;
    averageRating.textContent = host.averageRating.toFixed(1);

    //將發起人對應的約戰留言評論放入列表中
    reviewList.innerHTML = host.reviews
        .map(createReviewItem)
        .join("");
}

// 開啟燈箱
function openHistoryModal() {
    // 暫時先固定以host 101假資料呈現
    const host = hostReviewData[101];

    if (!host) {
        return;
    }

    renderHostHistory(host);

    historyModal.hidden = false;
    historyModal.classList.add("is-open");
    document.body.style.overflow = "hidden";
}

// 關閉燈箱
function closeHistoryModal() {
    historyModal.classList.remove("is-open");
    historyModal.hidden = true;
    document.body.style.overflow = "";
}

// 監聽邀約卡片的點擊事件，開啟約戰紀錄燈箱或申請加入燈箱
battleCardList.addEventListener("click", e => {

    // 點擊任一發起人按鈕時，可開啟對應歷史紀錄燈箱
    const hostBtn = e.target.closest(".hostBtn");

    if (hostBtn) {
        openHistoryModal();
        return;
    }

    // 點擊申請加入按鈕，開啟對應加入燈箱
    const applyBtn = e.target.closest(".applyBtn");

    if (applyBtn) {
        const battleId = Number(applyBtn.dataset.battleId);

        openApplyModal(battleId);
    }

});

// 點擊遮罩或關閉按鈕時關閉燈箱
historyModal.addEventListener("click", e => {
    const closeTarget = e.target.closest("[data-modal-close]");

    if (!closeTarget) {
        return;
    }

    closeHistoryModal();
});

// 按下Esc鍵也可關閉
document.addEventListener("keydown", e => {
    if (
        e.key === "Escape" &&
        historyModal.classList.contains("is-open")
    ) {
        closeHistoryModal();
    }
});



// 申請加入約戰燈箱設定
const applyModal = document.querySelector("#applyModal");
const applyAvatar = document.querySelector("#applyAvatar"); //燈箱中發起人頭像
const applyBattleTitle = document.querySelector("#apply-battleTitle"); //約戰標題
const applyBattleLocation = document.querySelector("#apply-battleLocation"); //約戰地區資訊
const applyContact = document.querySelector("#applyContact"); //參加人輸入的連絡資訊

// 將對應邀約資訊放進申請燈箱
function renderApplyModal(battle) {
    const host = hostReviewData[battle.hostId];

    applyAvatar.src = host.avatar;
    applyAvatar.alt = `${host.name}的會員頭像`;

    applyBattleTitle.textContent = battle.title;

    applyBattleLocation.textContent =
        `${battle.city}・${battle.district}`;
}

let currentApplyBattleId; //用來記住目前開啟加入的燈箱是哪一筆約戰

// 開啟申請加入燈箱
function openApplyModal(battleId) {
    const battle = battleData.find(battle => {
        return battle.battleId === battleId;
    });

    if (!battle) {
        return;
    }

    currentApplyBattleId = battleId;

    renderApplyModal(battle);

    // 每次開啟時，先清空先前輸入內容
    applyContact.value = "";

    applyModal.hidden = false;
    applyModal.classList.add("is-open");
    document.body.style.overflow = "hidden";

    // 開啟後讓游標直接進入輸入框
    applyContact.focus();
}

// 關閉申請加入燈箱
function closeApplyModal() {
    applyModal.classList.remove("is-open");
    applyModal.hidden = true;
    document.body.style.overflow = "";
}

// 點擊X、取消按鈕或背景遮罩時關閉燈箱
applyModal.addEventListener("click", e => {

    //當點擊目標含有當初HTML布局時加入的data-apply-close屬性時，就關閉燈箱
    const closeTarget = e.target.closest("[data-apply-close]");

    if (!closeTarget) {
        return;
    }

    closeApplyModal();
});

//確認加入約戰設定
const applyForm = document.querySelector("#applyForm");

applyForm.addEventListener("submit", e => {
    e.preventDefault();//停止跳分頁的預設動作

    const contact = applyContact.value.trim();

    if (!contact) {
        alert("請填寫你願意公開的聯絡資訊。");
        applyContact.focus();
        return;
    }

    const battle = battleData.find(battle => {
        return battle.battleId === currentApplyBattleId;
    });

    if (!battle) {
        alert("找不到這筆約戰資料。");
        return;
    }

    const isConfirmed = confirm(
        `確定要申請加入「${battle.title}」嗎？`
    );

    if (!isConfirmed) {
        return;
    }

    // demo時，先模擬資料狀態改變
    battle.status = "pending";
    battle.applicantContact = contact;
    battle.appliedAt = new Date().toISOString();

    updateAppliedCard(battle.battleId);

    closeApplyModal();

    alert("申請已送出，請等待發起人確認。");
});

// 當確認送出申請時，需更新邀約卡狀態
function updateAppliedCard(battleId) {
    const battleCard = battleCardList.querySelector(
        `.battleCard[data-battle-id="${battleId}"]`
    );

    if (!battleCard) {
        return;
    }
    //找到提出申請後那張邀約卡中的加入按鈕，並將原先的"申請加入"文字替換為"等待確認"
    const applyBtn = battleCard.querySelector(".applyBtn");

    if (!applyBtn) {
        return;
    }

    applyBtn.textContent = "等待確認";
    applyBtn.disabled = true;
}

//約戰卡統一篩選分類函式
const battleType = document.querySelector("#battleType"); //模式select欄位
const battleTarget = document.querySelector("#battleTarget");//對象select
const playerLevel = document.querySelector("#playerLevel");//程度select

const startDate = document.querySelector("#startDate");//起始日期input
const endDate = document.querySelector("#endDate");//結束日期input

function battleFilter() {
    const selectedMode = battleType.value; //選定對戰模式
    const selectedTarget = battleTarget.value; //選定對象
    const selectedLevel = playerLevel.value; // 選定玩家程度
    const selectedCity = citySelect.value;
    const selectedDistrict = districtSelect.value;
    const selectedStartDate = startDate.value; //篩選起始日期
    const selectedEndDate = endDate.value; //篩選截止日期

    //最後render出的約戰卡，需符合所有篩選條件
    const filteredBattles = battleData.filter(battle => {
        const isNotExpired =
            new Date(battle.deadline).getTime() > Date.now();

        const isMatching =
            battle.status === "matching";

        //當使用者沒有設定對戰模式時，則所有模式的約戰都會通過檢查；有設定模式時，才會比對卡片池中，有哪些卡片符合條件
        const matchesMode =
            !selectedMode || battle.mode === selectedMode;

        const matchesTarget =
            !selectedTarget || battle.target === selectedTarget;

        const matchesLevel =
            !selectedLevel || battle.level === selectedLevel;

        const matchesCity =
            !selectedCity || battle.city === selectedCity;

        const matchesDistrict =
            !selectedDistrict ||
            battle.district === selectedDistrict;

        const battleDate =
            new Date(battle.battleDate).getTime();

        //有設定起始搜索日期時，起始日期必須小於或等於約戰日期
        const matchesStartDate =
            !selectedStartDate ||
            battleDate >= new Date(selectedStartDate).getTime();

        const matchesEndDate =
            !selectedEndDate ||
            //因為篩選列的日期只有抓到幾月幾號，若未在後面加上23:59:59，時間點會被定在當日的0:0:0，這樣最終設定日期當天的約戰卡其實會抓不到
            battleDate <= new Date(
                `${selectedEndDate}T23:59:59`
            ).getTime();

        return (
            isNotExpired &&
            isMatching &&
            matchesMode &&
            matchesTarget &&
            matchesLevel &&
            matchesCity &&
            matchesDistrict &&
            matchesStartDate &&
            matchesEndDate
        );
    });
    // 每次更改篩選條件時，都將可顯示的卡片數量重新設定為6張
    visibleCardCount = cardsPerLoad;

    //選好篩選條件後，重新render約戰卡，並同步套用截止日期倒數函式
    renderBattleCards(filteredBattles);
    updateAllCountdowns();
}

//將所有頁面上的篩選分類欄位 select 或 input 集結成陣列，然後對陣列中的每個條件都綁定change事件，只要有設定任一篩選條件，都會跑一次約戰卡的過濾篩選函式
const filterElements = [
    battleType,
    battleTarget,
    playerLevel,
    citySelect,
    districtSelect,
    startDate,
    endDate
];

filterElements.forEach(element => {
    element.addEventListener("change", battleFilter);
});