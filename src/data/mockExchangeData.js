// mockExchangeData.js
// exc = 交換區
export const exchangeList = [
   // {
   //    id: 'exc1',
   //    userId: 112,
   //    type: 'beyblade',
   //    product_img: 'CX13_01.webp',
   //    title: '龍王閃擊',
   //    headshot: 'public/spinix_member_test1.png',
   //    name: 'Lone軍團長',
   //    date: '2026-07-26',
   //    city: '基隆市',
   //    district: '信義區',
   //    status: 'available',
   //    condition: 'good',
   //    isVisible: true, // true = 上架中，false = 已下架
   //    description: '關於交換戰鬥陀螺這件事，我們都可能想錯了，交換戰鬥陀螺，是很多人每天都在做的事，但真正把它做出意義的人，寥寥可數。 當我們認真審視交換戰鬥陀螺這件事，會發現它所牽涉的，遠比表面看來複雜得多。'
   // },
   // {
   //    id: 'exc2',
   //    userId: 999, // 測試用
   //    type: 'ratchet',
   //    product_img: 'BX_02.webp',
   //    title: '魔導致尊',
   //    headshot: 'public/spinix_member_test1.png',
   //    name: 'Lone軍團長',
   //    date: '2026-07-26',
   //    city: '台北市',
   //    district: '中正區',
   //    status: 'available',
   //    condition: 'new',
   //    isVisible: true, // true = 上架中，false = 已下架
   //    description: '測試文字。'
   // },
   {
      post_id: 'exc1',
      mem_id: 999,
      comm_mem_id: '', // 申請會員編號，預設空值
      type: 'ratchet',
      product_img: 'BX-28.webp',
      title: '魔導致尊',
      description: '關於交換戰鬥陀螺這件事，我們都可能想錯了，交換戰鬥陀螺，是很多人每天都在做的事，但真正把它做出意義的人，寥寥可數。 當我們認真審視交換戰鬥陀螺這件事，會發現它所牽涉的，遠比表面看來複雜得多。',
      want_item: '', // 想交換物品
      headshot: 'public/spinix_member_test1.png',
      name: 'Lone軍團長',
      post_contact: '0909123456',
      create_time: '2026-07-26',
      city: '台北市',
      district: '中正區',
      status: 'available',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   {
      post_id: 'exc2',
      mem_id: 112,
      comm_mem_id: '', // 申請會員編號，預設空值
      type: 'ratchet',
      product_img: 'BX_02.webp',
      title: '交換商品2',
      description: '測試文字。',
      want_item: '絕版XXX',
      headshot: 'public/spinix_member_test1.png',
      name: '風火輪',
      post_contact: '0909123456',
      create_time: '2026-07-26',
      city: '基隆市',
      district: '信義區',
      status: 'available',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   {
      post_id: 'exc3',
      mem_id: 999,
      comm_mem_id: '', // 申請會員編號，預設空值
      type: 'ratchet',
      product_img: 'BX-28.webp',
      title: '我是交換商品3',
      description: '關於交換戰鬥陀螺這件事，我們都可能想錯了，交換戰鬥陀螺，是很多人每天都在做的事，但真正把它做出意義的人，寥寥可數。 當我們認真審視交換戰鬥陀螺這件事，會發現它所牽涉的，遠比表面看來複雜得多。',
      want_item: '', // 想交換物品
      headshot: 'public/spinix_member_test1.png',
      name: 'Lone軍團長',
      post_contact: '0909123456',
      create_time: '2026-07-26',
      city: '台北市',
      district: '中正區',
      status: 'pending',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   {
      post_id: 'exc4',
      mem_id: 999,
      comm_mem_id: '104', // 申請會員編號，預設空值
      type: 'ratchet',
      product_img: 'BX-28.webp',
      title: '我是交換中商品4',
      description: '關於交換戰鬥陀螺這件事，我們都可能想錯了，交換戰鬥陀螺，是很多人每天都在做的事，但真正把它做出意義的人，寥寥可數。 當我們認真審視交換戰鬥陀螺這件事，會發現它所牽涉的，遠比表面看來複雜得多。',
      want_item: '', // 想交換物品
      headshot: 'public/spinix_member_test1.png',
      name: 'Lone軍團長',
      post_contact: '0909123456',
      create_time: '2026-07-26',
      city: '台北市',
      district: '中正區',
      status: 'exchanging',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
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
export const typeLabelMap = {
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
   // {
   //    id: 1,
   //    articleId: 'exc1', // 文章ID
   //    userId: 102,
   //    headshot: 'spinix_member_default.png',
   //    name: 'Lone軍團長',
   //    content: '我想用我的天平座來交換，可以嗎？',
   //    date: '2026-08-10',
   //    isVisible: true, // true = 上架中，false = 已下架
   //    applyStatus: 'waitingReply'   // 賣家還沒回覆

   // },
   // {
   //    id: 2,
   //    articleId: 'exc1', // 文章ID
   //    userId: 103,
   //    headshot: 'spinix_member_test2.jpg',
   //    name: '風火輪',
   //    content: '我有一隻限量款可以交換喔',
   //    date: '2026-08-11',
   //    isVisible: true, // true = 上架中，false = 已下架
   //    applyStatus: 'replied'        // 賣家已回覆選中，等這個人確認
   // },
   {
      comm_id: 1, // 留言ID
      post_id: 'exc1', // 回復交換案件文章ID
      mem_id: 103, // 留言會員ID
      headshot: 'spinix_member_test2.jpg',
      name: '我是申請交換會員',
      comm_cantact: 'LINE:lineline1234',
      content: '我有一隻限量款可以交換喔',
      create_time: '2026-08-11',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: false  // 是否被選擇，預設false
   },
   {
      comm_id: 2, // 留言ID
      post_id: 'exc1', // 回復交換案件文章ID
      mem_id: 104, // 留言會員ID
      headshot: 'spinix_member_default.png',
      name: '我是申請交換會員2',
      comm_cantact: 'LINE:lineline1234',
      content: '我拿XXX跟你交換',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: false  // 是否被選擇，預設false
   },
   {
      comm_id: 3, // 留言ID
      post_id: 'exc3', // 回復交換案件文章ID
      mem_id: 999, // 留言會員ID
      headshot: 'spinix_member_test3.png',
      name: '我是申請交換會員3',
      comm_cantact: 'LINE:lineline1234',
      content: '我拿AAA跟你交換',
      create_time: '2026-08-16',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   },
   {
      comm_id: 4, // 留言ID
      post_id: 'exc3', // 回復交換案件文章ID
      mem_id: 101, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是申請交換會員4',
      comm_cantact: 'LINE:lineline1234',
      content: '我拿YYY跟你交換',
      create_time: '2026-07-21',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: false  // 是否被選擇，預設false
   },
   {
      comm_id: 5, // 留言ID
      post_id: 'exc3', // 回復交換案件文章ID
      mem_id: 101, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是申請交換會員5',
      comm_cantact: 'LINE:lineline1234',
      content: '我拿ZZZ跟你交換',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: false  // 是否被選擇，預設false
   },
   {
      comm_id: 6, // 留言ID
      post_id: 'exc4', // 回復交換案件文章ID
      mem_id: 104, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是申請交換會員6',
      comm_cantact: 'LINE:lineline1234',
      content: '我已被版主做選擇',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   }
];
// 「我提出的申請」用的狀態中文對照表（跟文章狀態脫鉤，是「我」這則申請的結果）
export const applyStatusLabelMap = {
   waitingReply: '等待回復',
   replied: '已回復',
   exchanging: '交換中',
   completed: '交換完成'
};