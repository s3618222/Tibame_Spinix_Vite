//會員中心-我的申訴假資料存放
const myAppealData = [
  {
    id: "A001",
    type: "交易糾紛",
    status: "pending",
    createdAt: "2026-07-01",
    reporter: "會員 A（ID:0012）",
    target: "會員 B（ID:0098）",
    content:
      "對方收款後未依約寄出商品，多次聯繫未讀不回，希望協助處理退款。",
    images: 2,
    result: "",
    resultDate: ""
  },
  {
    id: "A002",
    type: "論壇糾紛",
    status: "closed",
    createdAt: "2026-07-02",
    reporter: "會員 A（ID:0012）",
    target: "會員 C（ID:0105）",
    content: "對方在文章留言區持續使用不當言語辱罵，已截圖存證。",
    images: 3,
    result: "經審核後對方言論已違反社群規範，帳號已予以警告並下架相關留言。",
    resultDate: "2026-07-05"
  },
  {
    id: "A003",
    type: "約戰糾紛",
    status: "pending",
    createdAt: "2026-07-03",
    reporter: "會員 A（ID:0012）",
    target: "會員 D（ID:0231）",
    content: "約定的約戰時間到場後對方未出現，也未提前告知取消。",
    images: 1,
    result: "",
    resultDate: ""
  },
  {
    id: "A004",
    type: "交易糾紛",
    status: "closed",
    createdAt: "2026-07-04",
    reporter: "會員 A（ID:0012）",
    target: "會員 E（ID:0076）",
    content: "收到的商品與刊登照片明顯不符，懷疑賣家刻意隱瞞瑕疵。",
    images: 3,
    result: "經雙方溝通後賣家已同意退款，款項已於三個工作天內退回。",
    resultDate: "2026-07-08"
  }
];

export default myAppealData;
