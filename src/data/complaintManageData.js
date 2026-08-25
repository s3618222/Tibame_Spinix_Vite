// 後台「申訴管理」假資料
// 每筆為三張申訴表（battle_appeal / appeal_exchange / appeal_forum）
// 合併後的「統一列」，欄位形狀對齊未來 UNION ALL 後端回傳（見 php/member/my_appeal_get.php）
//   sourceType: 'battle' | 'exchange' | 'forum'（來源表）
//   status:     'pending' | 'confirmed' | 'rejected'（對應 enum PENDING/CONFIRMED/REJECTED）
const complaintManageData = [
  {
    id: "A010",
    sourceType: "battle",
    type: "對戰",
    complainant: "會員1",
    respondent: "會員2",
    status: "pending",
    createdAt: "2026-07-01 09:12",
    handler: "未指派",
    content:
      "約定的約戰時間到場後對方未出現，也未提前告知取消，導致我白跑一趟，希望協助處理。",
    evidence: [],
    handleResult: "",
    handleNote: ""
  },
  {
    id: "A011",
    sourceType: "battle",
    type: "對戰",
    complainant: "會員3",
    respondent: "會員4",
    status: "pending",
    createdAt: "2026-07-01 14:22",
    handler: "未指派",
    content: "對方在對戰結果回報時填寫不實，明明是我獲勝卻回報為對方勝出。",
    evidence: [],
    handleResult: "",
    handleNote: ""
  },
  {
    id: "A012",
    sourceType: "exchange",
    type: "二手交換",
    complainant: "會員5",
    respondent: "會員6",
    status: "confirmed",
    createdAt: "2026-07-02 10:05",
    handler: "管理員B",
    content: "對方收款後未依約寄出商品，多次聯繫未讀不回，希望協助處理退款。",
    evidence: [],
    handleResult: "累計違規次數+1",
    handleNote: "經查對方確實未出貨且逾期未回應，違規成立，已累計違規並通知雙方。"
  },
  {
    id: "A013",
    sourceType: "forum",
    type: "論壇",
    complainant: "會員7",
    respondent: "會員8",
    status: "pending",
    createdAt: "2026-07-03 16:40",
    handler: "未指派",
    content: "對方在文章留言區持續使用不當言語辱罵，已截圖存證，請協助查核。",
    evidence: [],
    handleResult: "",
    handleNote: ""
  },
  {
    id: "A014",
    sourceType: "exchange",
    type: "二手交換",
    complainant: "會員9",
    respondent: "會員1",
    status: "rejected",
    createdAt: "2026-07-04 11:30",
    handler: "管理員A",
    content: "收到的商品與刊登照片不符，懷疑賣家刻意隱瞞瑕疵。",
    evidence: [],
    handleResult: "駁回申訴",
    handleNote: "經雙方提供的照片比對，商品狀態與描述相符，屬正常使用痕跡，申訴不成立。"
  },
  {
    id: "A015",
    sourceType: "forum",
    type: "論壇",
    complainant: "會員2",
    respondent: "會員4",
    status: "confirmed",
    createdAt: "2026-07-05 08:50",
    handler: "管理員A",
    content: "這篇貼文的保養建議根本是錯的，會損壞軸心，已誤導不少新手。",
    evidence: [],
    handleResult: "累計違規次數+1",
    handleNote: "經審核該建議確實有誤且具誤導性，已請作者更正並加註警語，違規成立。"
  },
  {
    id: "A016",
    sourceType: "exchange",
    type: "二手交換",
    complainant: "會員A",
    respondent: "會員B",
    status: "pending",
    createdAt: "2026-07-05 14:22",
    handler: "未指派",
    content:
      "申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容申訴內容。",
    evidence: [],
    handleResult: "",
    handleNote: ""
  },
  {
    id: "A017",
    sourceType: "battle",
    type: "對戰",
    complainant: "會員6",
    respondent: "會員7",
    status: "confirmed",
    createdAt: "2026-07-06 19:15",
    handler: "管理員B",
    content: "對方多次無故取消已確認的約戰，影響其他玩家的排程安排。",
    evidence: [],
    handleResult: "累計違規次數+1",
    handleNote: "查證對方近一週內三次於確認後取消約戰，違規成立。"
  },
  {
    id: "A018",
    sourceType: "forum",
    type: "論壇",
    complainant: "會員8",
    respondent: "會員9",
    status: "rejected",
    createdAt: "2026-07-07 13:20",
    handler: "管理員A",
    content: "這則留言散布不實資訊，內容誤導其他玩家購買管道，請協助查核。",
    evidence: [],
    handleResult: "駁回申訴",
    handleNote: "經查該留言為個人使用心得分享，未涉及不實導購，申訴不成立。"
  },
  {
    id: "A019",
    sourceType: "exchange",
    type: "二手交換",
    complainant: "會員3",
    respondent: "會員5",
    status: "pending",
    createdAt: "2026-07-08 22:03",
    handler: "未指派",
    content: "交換過程中對方臨時要求加價，與原先約定不符，希望協助釐清。",
    evidence: [],
    handleResult: "",
    handleNote: ""
  }
];

export default complaintManageData;
