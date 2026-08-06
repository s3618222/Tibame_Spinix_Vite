//後台-約戰專區假資料存放

const battleManageData = [
  {
    battleId: 1,
    title: "假日陀螺交流場",
    hostName: "Bill0142",
    city: "taoyuan",
    cityLabel: "桃園市",
    district: "中壢區",
    mode: "casual",
    status: "matching",
    is_show: true,
    battleDate: "2026-08-16",

    //詳情燈箱用資訊
    description: "周末休閒場，新手也歡迎參加，一起進步~",
    battleTime: "14:00",
    deadline: "2026-08-13 12:00",
    meetingPlace: "中壢火車站 1 樓大門外的廣場",
    playerLevel: "不限",
    targetAudience: "不限",

    participantName: null,
    hostContact: "LINE ID：b12345",
    participantContact: null,

    winner: null,
    createdAt: "2026-08-14 14:32",

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 2,
    title: "競技模式練習戰",
    hostName: "Andy746",
    city: "taipei",
    cityLabel: "台北市",
    district: "中山區",
    mode: "competitive",
    status: "success",
    is_show: true,
    createdAt: "2026-07-30 18:20",
    battleDate: "2026-08-13",

    description: "想找熟悉競技規則的玩家練習，預計採三戰兩勝制。",
    battleTime: "19:00",
    deadline: "2026-08-11 22:00",
    meetingPlace: "台北市中山運動中心一樓入口",
    playerLevel: "中階以上",
    targetAudience: "不限",

    participantName: "赤焰龍皇",
    hostContact: "LINE ID：andy746",
    participantContact: "Discord：red_dragon",

    winner: 1,
    coverImage: "/battle_card_test1.jpg"
  },
  {
    battleId: 3,
    title: "新手友善對戰",
    hostName: "眼睛不眨",
    city: "new-taipei",
    cityLabel: "新北市",
    district: "板橋區",
    mode: "casual",
    status: "failed",
    is_show: true,
    createdAt: "2026-07-28 11:15",
    battleDate: "2026-08-01",

    // 詳情燈箱用資訊
    description: "歡迎剛接觸戰鬥陀螺的新手參加，沒有經驗也沒關係。",
    battleTime: "14:30",
    deadline: "2026-07-31 18:00",
    meetingPlace: "板橋車站一樓北二門旁",
    playerLevel: "新手",
    targetAudience: "不限",

    participantName: null,
    hostContact: "LINE ID：blink_bey",
    participantContact: null,

    winner: null,

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 4,
    title: "台北週末陀螺交流",
    hostName: "陀螺小宇",
    city: "taipei",
    cityLabel: "台北市",
    district: "中山區",
    mode: "casual",
    status: "pending",
    is_show: true,
    createdAt: "2026-08-03 09:40",
    battleDate: "2026-08-20",

    // 詳情燈箱用資訊
    description: "週末輕鬆交流陀螺配置，新手與一般玩家都歡迎參加。",
    battleTime: "15:00",
    deadline: "2026-08-18 20:00",
    meetingPlace: "捷運中山站四號出口外廣場",
    playerLevel: "不限",
    targetAudience: "不限",

    participantName: "小翔",
    hostContact: "LINE ID：bey_yu",
    participantContact: "0912-456-780",

    winner: null,

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 5,
    title: "新北競技積分對戰",
    hostName: "烈焰阿哲",
    city: "new-taipei",
    cityLabel: "新北市",
    district: "板橋區",
    mode: "competitive",
    status: "success",
    is_show: true,
    createdAt: "2026-07-30 13:25",
    battleDate: "2026-08-02",

    // 詳情燈箱用資訊
    description: "使用正式競技規則進行積分對戰，請自備三顆參賽陀螺。",
    battleTime: "13:00",
    deadline: "2026-07-31 23:00",
    meetingPlace: "板橋第一運動場正門入口",
    playerLevel: "進階",
    targetAudience: "不限",

    participantName: "鋼鐵戰魂",
    hostContact: "Discord：fire_azhe",
    participantContact: "LINE ID：steel_soul",

    winner: 0,

    coverImage: "/battle_card_test2.jpg"
  },
  {
    battleId: 6,
    title: "桃園新手友善交流場",
    hostName: "旋風小凱",
    city: "taoyuan",
    cityLabel: "桃園市",
    district: "桃園區",
    mode: "casual",
    status: "matching",
    is_show: true,
    createdAt: "2026-08-04 16:10",
    battleDate: "2026-08-28",

    // 詳情燈箱用資訊
    description: "以輕鬆交流為主，歡迎第一次參加約戰的新手玩家。",
    battleTime: "14:00",
    deadline: "2026-08-26 21:00",
    meetingPlace: "桃園市立圖書館總館一樓入口",
    playerLevel: "新手",
    targetAudience: "不限",

    participantName: null,
    hostContact: "LINE ID：wind_kai",
    participantContact: null,

    winner: null,

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 7,
    title: "下班後陀螺練習賽",
    hostName: "鋼鐵戰魂",
    city: "taipei",
    cityLabel: "台北市",
    district: "信義區",
    mode: "competitive",
    status: "failed",
    is_show: true,
    createdAt: "2026-08-02 12:30",
    battleDate: "2026-08-04",

    // 詳情燈箱用資訊
    description: "下班後進行競技模式練習，主要測試新的防禦型配置。",
    battleTime: "19:30",
    deadline: "2026-08-03 18:00",
    meetingPlace: "捷運市政府站二號出口",
    playerLevel: "中階以上",
    targetAudience: "成年人",

    participantName: null,
    hostContact: "LINE ID：steel_soul",
    participantContact: null,

    winner: null,

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 8,
    title: "板橋假日休閒對戰",
    hostName: "阿任",
    city: "new-taipei",
    cityLabel: "新北市",
    district: "板橋區",
    mode: "casual",
    status: "matching",
    is_show: false,
    createdAt: "2026-07-25 10:45",
    battleDate: "2026-08-08",

    // 詳情燈箱用資訊
    description: "假日休閒交流場，不限制陀螺類型與玩家經驗。",
    battleTime: "15:30",
    deadline: "2026-08-06 20:00",
    meetingPlace: "板橋435藝文特區服務中心外",
    playerLevel: "不限",
    targetAudience: "不限",

    participantName: null,
    hostContact: "Instagram：bey_ren",
    participantContact: null,

    winner: null,

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 9,
    title: "中壢陀螺玩家挑戰賽",
    hostName: "赤焰龍皇",
    city: "taoyuan",
    cityLabel: "桃園市",
    district: "中壢區",
    mode: "competitive",
    status: "pending",
    is_show: true,
    createdAt: "2026-08-01 20:15",
    battleDate: "2026-08-15",

    // 詳情燈箱用資訊
    description: "尋找實力相近的玩家進行挑戰，採三戰兩勝競技規則。",
    battleTime: "16:00",
    deadline: "2026-08-13 22:00",
    meetingPlace: "中壢銀河廣場服務台旁",
    playerLevel: "中階以上",
    targetAudience: "不限",

    participantName: "蒼羽風狼",
    hostContact: "Discord：red_dragon",
    participantContact: "LINE ID：blue_wolf",

    winner: null,

    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 10,
    title: "大安區新手交流對戰",
    hostName: "小羽",
    city: "taipei",
    cityLabel: "台北市",
    district: "大安區",
    mode: "casual",
    status: "success",
    is_show: true,
    createdAt: "2026-08-05 08:30",
    battleDate: "2026-08-11",

    // 詳情燈箱用資訊
    description: "以新手交流與配置分享為主，不在意輸贏，輕鬆參加即可。",
    battleTime: "17:00",
    deadline: "2026-08-09 20:00",
    meetingPlace: "大安森林公園捷運站五號出口",
    playerLevel: "新手",
    targetAudience: "不限",

    participantName: "陀螺阿哲",
    hostContact: "LINE ID：little_yu",
    participantContact: "0918-235-467",

    // 休閒模式不紀錄勝者
    winner: null,
    coverImage: "/battle_card_default.jpg"
  },
  {
    battleId: 11,
    title: "新莊競技模式約戰",
    hostName: "風狼玩家",
    city: "new-taipei",
    cityLabel: "新北市",
    district: "新莊區",
    mode: "competitive",
    status: "failed",
    is_show: true,
    createdAt: "2026-07-20 15:30",
    battleDate: "2026-08-01",

    // 詳情燈箱用資訊
    description: "進行正式競技模式約戰，歡迎使用平衡型或攻擊型配置的玩家。",
    battleTime: "14:00",
    deadline: "2026-07-30 21:00",
    meetingPlace: "新莊體育館正門入口",
    playerLevel: "中階以上",
    targetAudience: "不限",

    participantName: null,
    hostContact: "LINE ID：wind_wolf",
    participantContact: null,

    winner: null,

    coverImage: "/battle_card_default.jpg"
  }
];

export default battleManageData;