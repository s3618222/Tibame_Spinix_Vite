// mockExchangeData.js
// exc = 交換區
export const exchangeList = [
   {
      post_id: 'exc1',
      mem_id: 999,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
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
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
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
      mem_id: 111,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
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
      // comm_mem_id: '104', // 申請會員編號，預設空值
      exchange_comm_id: 6,   // 改成存留言ID，尚未定案時是 null
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
   },
   {
      post_id: 'exc5',
      mem_id: 112,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
      type: 'blade',
      product_img: 'BX_02.webp',
      title: '交換商品5',
      description: '測試文字。',
      want_item: '絕版XXX',
      headshot: 'public/spinix_member_test1.png',
      name: '風火輪',
      post_contact: '0909123456',
      create_time: '2026-07-16',
      city: '基隆市',
      district: '信義區',
      status: 'available',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   // 交換中案件
   {
      post_id: 'exc6',
      mem_id: 112,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: 8,   // 改成存留言ID，尚未定案時是 null
      type: 'ratchet',
      product_img: 'BX_02.webp',
      title: '交換商品6',
      description: '測試文字。',
      want_item: '絕版XXX',
      headshot: 'public/spinix_member_test1.png',
      name: '風火輪',
      post_contact: '0909123456',
      create_time: '2026-08-26',
      city: '基隆市',
      district: '信義區',
      status: 'exchanging',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   // 交換完成案件
   {
      post_id: 'exc7',
      mem_id: 112,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: 9,   // 改成存留言ID，尚未定案時是 null
      type: 'others',
      product_img: 'BX_02.webp',
      title: '交換商品7',
      description: '測試文字。',
      want_item: '絕版XXX',
      headshot: 'public/spinix_member_test1.png',
      name: '風火輪',
      post_contact: '0909123456',
      create_time: '2026-06-26',
      city: '基隆市',
      district: '信義區',
      status: 'completed',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: true, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   // 以選擇等待對方回覆
   {
      post_id: 'exc8',
      mem_id: 999,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
      type: 'bit',
      product_img: 'BX_02.webp',
      title: '交換商品7',
      description: '測試文字。',
      want_item: '絕版XXX',
      headshot: 'public/spinix_member_test1.png',
      name: '風火輪',
      post_contact: '0909123456',
      create_time: '2026-07-26',
      city: '基隆市',
      district: '信義區',
      status: 'pending',
      condition: 'new',
      is_show: true, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '' // 下架原因
   },
   // 被下架文章
   {
      post_id: 'exc9',
      mem_id: 999,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
      type: 'beyblade',
      product_img: 'BX_02.webp',
      title: '我是被下架文章',
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
      is_show: false, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '文章因包含不適當言論或違規資訊，已依平台規範停止公開顯示。' // 下架原因
   },
   {
      post_id: 'exc10',
      mem_id: 999,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
      type: 'beyblade',
      product_img: 'BX_02.webp',
      title: '我是被下架文章',
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
      is_show: false, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '文章因包含不適當言論或違規資訊，已依平台規範停止公開顯示。' // 下架原因
   },
   {
      post_id: 'exc11',
      mem_id: 999,
      // comm_mem_id: '', // 申請會員編號，預設空值
      exchange_comm_id: null,   // 改成存留言ID，尚未定案時是 null
      type: 'beyblade',
      product_img: 'BX_02.webp',
      title: '我是被下架文章',
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
      is_show: false, // true = 上架中，false = 已下架
      is_exchanged: false, // 是否被交換
      post_pic: [],
      remove_reason: '文章因包含不適當言論或違規資訊，已依平台規範停止公開顯示。' // 下架原因
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
   {
      comm_id: 1, // 留言ID
      post_id: 'exc1', // 回復交換案件文章ID
      mem_id: 103, // 留言會員ID
      headshot: 'spinix_member_test2.jpg',
      name: '我是申請交換會員',
      comm_contact: 'LINE:lineline1234',
      content: '我有一隻限量款可以交換喔',
      create_time: '2026-08-11',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: false  // 是否被選擇，預設false
   },
   {
      comm_id: 2, // 留言ID
      post_id: 'exc2', // 回復交換案件文章ID
      mem_id: 104, // 留言會員ID
      headshot: 'spinix_member_default.png',
      name: '我是申請交換會員2',
      comm_contact: 'LINE:lineline1234',
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
      name: '我是999申請交換會員3',
      comm_contact: 'LINE:lineline1234',
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
      comm_contact: 'LINE:lineline1234',
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
      comm_contact: 'LINE:lineline1234',
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
      comm_contact: 'LINE:lineline1234',
      content: '我已被版主做選擇',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   },
   {
      comm_id: 7, // 留言ID
      post_id: 'exc5', // 回復交換案件文章ID
      mem_id: 999, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是999申請交換會員6',
      comm_contact: 'LINE:lineline1234',
      content: '6666666666666666',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   },
   // 交換中案件
   {
      comm_id: 8, // 留言ID
      post_id: 'exc6', // 回復交換案件文章ID
      mem_id: 999, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是999申請交換會員6',
      comm_contact: 'LINE:lineline1234',
      content: '6666666666666666',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   },
   // 交換完成案件
   {
      comm_id: 9, // 留言ID
      post_id: 'exc7', // 回復交換案件文章ID
      mem_id: 999, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是999申請交換會員6',
      comm_contact: 'LINE:lineline1234',
      content: '6666666666666666',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   },
   // 以選擇，等待回覆
   {
      comm_id: 10, // 留言ID
      post_id: 'exc8', // 回復交換案件文章ID
      mem_id: 111, // 留言會員ID
      headshot: 'sop02.png',
      name: '我是111申請交換會員',
      comm_contact: 'LINE:lineline1234',
      content: '6666666666666666',
      create_time: '2026-08-19',
      is_show: true, // true = 上架中，false = 已下架
      remove_reason: '', // 下架原因
      is_choose: true  // 是否被選擇，預設false
   },
   // 被下架留言
   {
      comm_id: 11, // 留言ID
      post_id: 'exc9', // 回復交換案件文章ID
      mem_id: 999, // 留言會員ID
      headshot: 'sop02.png',
      name: 'testuser',
      comm_contact: 'LINE:lineline1234',
      content: '我是被下架留言',
      create_time: '2026-08-19',
      is_show: false, // true = 上架中，false = 已下架
      remove_reason: '有激烈言論，被下架', // 下架原因
      is_choose: false  // 是否被選擇，預設false
   }
];
// 「我提出的申請」用的狀態中文對照表（跟文章狀態脫鉤，是「我」這則申請的結果）
export const applyStatusLabelMap = {
   available: '申請中',
   pending: '已回覆',
   exchanging: '交換中',
   completed: '交換完成'
};


export function replyExchange(exchangeList, { postId, applyId, posterName }) {
   const isConfirm = window.confirm(
      `${posterName}對你的交換提議有興趣!\n是否確認交換?按下確認後將可以查看雙方的聯絡資訊`
   );

   if (isConfirm) {
      const targetArticle = exchangeList.find(article => article.post_id === postId);

      if (targetArticle) {
         targetArticle.exchange_comm_id = applyId;
         targetArticle.status = 'exchanging';
      }

      window.alert('確認成功!現在可以查看對方的聯絡資訊了');
      return true;
   }

   return false;
}

export function completeExchange(exchangeList, { postId }) {
   const targetArticle = exchangeList.find(article => article.post_id === postId);
   if (targetArticle) {
      targetArticle.status = 'completed';
   }
}

export function cancelExchange(exchangeList, { postId }) {
   const targetArticle = exchangeList.find(article => article.post_id === postId);
   if (targetArticle) {
      targetArticle.status = 'available';
      targetArticle.exchange_comm_id = null;
   }
}