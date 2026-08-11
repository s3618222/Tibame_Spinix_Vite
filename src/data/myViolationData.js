//會員中心-違規紀錄假資料存放
const myViolationData = [
  {
    id: "A001",
    type: "交易糾紛",
    punishment: "禁言3天",
    reportedAt: "2026-07-01",
    target: "會員 B（ID:0098）",
    content:
      "收到檢舉指出刊登商品與實際狀況不符，經審核後屬實，已對帳號進行處分。",
    images: 2,
    result: "帳號已依規定禁言 3 天，期間內無法於論壇與交換專區發文。",
    resultDate: "2026-07-05"
  },
  {
    id: "A005",
    type: "論壇糾紛",
    punishment: "留言下架",
    reportedAt: "2026-07-06",
    target: "會員 F（ID:0142）",
    content: "於論壇文章留言區使用不當言語，經檢舉查證後違反社群規範。",
    images: 1,
    result: "違規留言已下架，並對帳號發出書面警告一次。",
    resultDate: "2026-07-08"
  },
  {
    id: "A007",
    type: "約戰糾紛",
    punishment: "停權7天",
    reportedAt: "2026-07-10",
    target: "會員 G（ID:0203）",
    content: "多次約戰未到場也未提前告知取消，經多筆檢舉查證屬實。",
    images: 3,
    result: "帳號約戰配對功能停權 7 天，期滿後自動恢復。",
    resultDate: "2026-07-13"
  },
  {
    id: "A009",
    type: "交易糾紛",
    punishment: "禁止刊登交換物品",
    reportedAt: "2026-07-15",
    target: "會員 H（ID:0088）",
    content: "刊登商品與描述明顯不符，且拒絕依規定辦理退款。",
    images: 2,
    result: "帳號交換專區刊登權限已暫停，需完成申訴審核後始得恢復。",
    resultDate: "2026-07-18"
  }
];

export default myViolationData;
