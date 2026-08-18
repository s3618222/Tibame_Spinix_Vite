// mockExchangeData.js
// exc = 交換區
export const exchangeList = [
   {
      id: 'exc1',
      userId: 112,
      type: 'beyblade',
      product_img: 'CX13_01.webp',
      title: '龍王閃擊',
      headshot: 'public/spinix_member_test1.png',
      name: 'Lone軍團長',
      date: '2026-07-26',
      city: '基隆市',
      district: '信義區',
      status: 'available',
      condition: 'good',
      isVisible: true, // true = 上架中，false = 已下架
      description: '關於交換戰鬥陀螺這件事，我們都可能想錯了，交換戰鬥陀螺，是很多人每天都在做的事，但真正把它做出意義的人，寥寥可數。 當我們認真審視交換戰鬥陀螺這件事，會發現它所牽涉的，遠比表面看來複雜得多。'
   },
   {
      id: 'exc2',
      userId: 999, // 測試用
      type: 'ratchet',
      product_img: 'BX_02.webp',
      title: '魔導致尊',
      headshot: 'public/spinix_member_test1.png',
      name: 'Lone軍團長',
      date: '2026-07-26',
      city: '台北市',
      district: '中正區',
      status: 'available',
      condition: 'new',
      isVisible: true, // true = 上架中，false = 已下架
      description: '測試文字。'
   }
];

// 文章本身的狀態中文對照表（可交換／交換中／待確認／交換完成）
export const statusLabelMap = {
   available: '可交換',
   exchanging: '交換中',
   pending: '待確認',
   completed: '交換完成'
};

// 陀螺類別
export const  typeLabelMap = {
   beyblade: '陀螺本體',
   blade: '戰刃',
   ratchet: '固鎖',
   bit: '軸心',
   others: '其他'
};

// 商品狀態
export const conditionLabelMap = {
   new: '全新',
   good: '二手-良好',
   fair: '二手-有使用痕跡'
};
// ------------------------------------------------------------------
// 留言＝申請交換
// 一篇文章可以有多人留言（=多人提出申請），
// 但賣家只會選其中一則留言作為交換對象
// ------------------------------------------------------------------
// fakeComments 的 applyStatus 改用上面四種值
export const fakeComments = [
   {
      id: 1,
      articleId: 'exc1', // 文章ID
      userId: 102,
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      content: '我想用我的天平座來交換，可以嗎？',
      date: '2026-08-10',
      isVisible: true, // true = 上架中，false = 已下架
      applyStatus: 'waitingReply'   // 賣家還沒回覆
      
   },
   {
      id: 2,
      articleId: 'exc1', // 文章ID
      userId: 103,
      headshot: 'spinix_member_test2.jpg',
      name: '風火輪',
      content: '我有一隻限量款可以交換喔',
      date: '2026-08-11',
      isVisible: true, // true = 上架中，false = 已下架
      applyStatus: 'replied'        // 賣家已回覆選中，等這個人確認
   },
];
// 「我提出的申請」用的狀態中文對照表（跟文章狀態脫鉤，是「我」這則申請的結果）
export const applyStatusLabelMap = {
   waitingReply: '等待回復',
   replied: '已回復',
   exchanging: '交換中',
   completed: '交換完成'
};