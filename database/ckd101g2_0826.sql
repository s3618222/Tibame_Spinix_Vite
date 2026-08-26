-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- 主機： localhost:8889
-- 產生時間： 2026-08-26 02:56:23
-- 伺服器版本： 5.7.24
-- PHP 版本： 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫: `spinix`
--

-- --------------------------------------------------------

--
-- 資料表結構 `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(10) UNSIGNED NOT NULL COMMENT '管理員ID',
  `account` varchar(50) NOT NULL COMMENT '登入帳號',
  `password` varchar(50) NOT NULL COMMENT '密碼',
  `name` varchar(50) NOT NULL COMMENT '管理員名稱',
  `create_time` datetime NOT NULL COMMENT '建立時間',
  `admin_type` enum('超級管理員','一般管理員') NOT NULL COMMENT '管理員類型',
  `admin_state` enum('在職','離職') NOT NULL COMMENT '管理員狀態'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理員列表';

--
-- 傾印資料表的資料 `admin`
--

INSERT INTO `admin` (`admin_id`, `account`, `password`, `name`, `create_time`, `admin_type`, `admin_state`) VALUES
(1, 'admin1', '123456', 'admin1', '2026-08-18 12:12:08', '超級管理員', '在職');

-- --------------------------------------------------------

--
-- 資料表結構 `appeal_exchange`
--

CREATE TABLE `appeal_exchange` (
  `ae_id` int(10) UNSIGNED NOT NULL COMMENT '交換申訴ID',
  `post_id` int(11) DEFAULT NULL COMMENT '交換案件ID',
  `comm_id` int(10) UNSIGNED DEFAULT NULL COMMENT '交換申請ID',
  `complainant_mem_id` int(10) UNSIGNED NOT NULL COMMENT '申訴人ID',
  `respondent_mem_id` int(10) UNSIGNED NOT NULL COMMENT '被申訴人ID',
  `admin_id` int(10) UNSIGNED DEFAULT NULL COMMENT '審核員ID',
  `ae_content` text NOT NULL COMMENT '申訴案件內容',
  `ae_status` enum('PENDING','CONFIRMED','REJECTED') NOT NULL DEFAULT 'PENDING' COMMENT '申訴案件狀態',
  `create_time` datetime NOT NULL COMMENT '申訴時間',
  `ae_evidence` varchar(255) DEFAULT NULL COMMENT '證據截圖',
  `responded_at` datetime DEFAULT NULL COMMENT '管理員回覆時間',
  `responded_text` varchar(200) NOT NULL COMMENT '管理員回復內容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='申訴案件-交換';

-- --------------------------------------------------------

--
-- 資料表結構 `appeal_forum`
--

CREATE TABLE `appeal_forum` (
  `af_id` int(10) UNSIGNED NOT NULL COMMENT '貼文申訴ID',
  `art_id` int(10) UNSIGNED DEFAULT NULL COMMENT '被舉報貼文ID',
  `msg_id` int(10) UNSIGNED DEFAULT NULL COMMENT '被舉報留言ID',
  `complainant_mem_id` int(10) UNSIGNED NOT NULL COMMENT '申訴人ID',
  `respondent_mem_id` int(10) UNSIGNED NOT NULL COMMENT '被申訴人ID',
  `admin_id` int(10) UNSIGNED DEFAULT NULL COMMENT '審核員ID',
  `af_content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '申訴案件內容',
  `af_status` enum('PENDING','CONFIRMED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING' COMMENT '申訴案件狀態',
  `create_time` datetime NOT NULL COMMENT '申訴時間',
  `af_evidence` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '證據截圖',
  `responded_at` datetime DEFAULT NULL COMMENT '管理員回覆時間',
  `responded_text` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '管理員回復內容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='申訴案件-論壇';

--
-- 傾印資料表的資料 `appeal_forum`
--

INSERT INTO `appeal_forum` (`af_id`, `art_id`, `msg_id`, `complainant_mem_id`, `respondent_mem_id`, `admin_id`, `af_content`, `af_status`, `create_time`, `af_evidence`, `responded_at`, `responded_text`) VALUES
(1, NULL, 5, 4, 9, 1, '這則留言內容不實，指控我的文章亂寫，但我文章來源都有附上，希望管理員協助處理。', 'PENDING', '2026-08-13 09:00:00', NULL, NULL, NULL),
(2, 6, NULL, 2, 4, 1, '這篇被下架的文章其實沒有廣告連結，是誤判，申請重新審核。', 'REJECTED', '2026-08-14 10:15:00', 'evidence_appeal_02.jpg', '2026-08-15 09:30:00', '經複查確認文章內確實含有導購連結，維持下架決定。'),
(3, NULL, 2, 3, 5, NULL, '這則留言散布不實資訊，內容誤導其他玩家購買管道，請協助查核。', 'PENDING', '2026-08-15 16:40:00', NULL, NULL, NULL),
(4, 4, NULL, 8, 6, 1, '這篇貼文的保養建議根本是錯的，會損壞軸心，已誤導不少新手。', 'CONFIRMED', '2026-08-16 13:20:00', NULL, '2026-08-17 10:00:00', '經審核該建議確實有誤，已請作者更正並加註警語。'),
(5, NULL, 3, 2, 1, NULL, '這則留言重複洗版，疑似機器人帳號行為。', 'PENDING', '2026-08-17 18:05:00', 'evidence_appeal_05.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `article`
--

CREATE TABLE `article` (
  `art_id` int(10) UNSIGNED NOT NULL COMMENT '貼文編號',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標題',
  `category` enum('announcement','unboxing','event','chat','strategy','faq') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章類別',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '內文',
  `mem_id` int(10) UNSIGNED NOT NULL COMMENT '會員ID',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '顯示狀態',
  `create_time` datetime NOT NULL COMMENT '發文時間',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '圖片',
  `remove_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '下架原因'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='論壇貼文表';

--
-- 傾印資料表的資料 `article`
--

INSERT INTO `article` (`art_id`, `title`, `category`, `content`, `mem_id`, `is_show`, `create_time`, `pic`, `remove_reason`) VALUES
(1, '2026年度大賽規章公告', 'announcement', '請所有參賽選手務必於期限內完成設備檢核...', 1, 1, '2026-08-01 09:00:00', NULL, NULL),
(2, '【開箱】黃金版天翼戰神實拍', 'unboxing', '等了三個月終於從日本寄過來了...', 3, 1, '2026-08-05 14:20:00', 'article_pic_02.jpg', NULL),
(3, '台北站賽程與交通資訊', 'event', '本次報到地點位於二樓多功能大廳...', 5, 1, '2026-08-08 10:00:00', 'article_pic_03.jpg', NULL),
(4, '大家平常都怎麼保養軸心的？', 'chat', '最近發現我的軸心轉起來聲音怪怪的...', 6, 0, '2026-08-10 20:15:00', NULL, NULL),
(5, '進階配裝：攻擊型 vs 平衡型分析', 'strategy', '這篇整理近期幾場對戰中的配裝心得...', 7, 1, '2026-08-12 22:40:00', NULL, NULL),
(6, '新手發問：固鎖環要怎麼挑？', 'faq', '剛入坑想請教大家固鎖環選擇的原則...', 4, 0, '2026-08-13 11:05:00', NULL, '內容含廣告連結，經檢舉下架');

-- --------------------------------------------------------

--
-- 資料表結構 `battle_appeal`
--

CREATE TABLE `battle_appeal` (
  `BATTLE_APPEAL_ID` int(10) UNSIGNED NOT NULL COMMENT '對戰申訴編號',
  `BATTLE_ID` int(10) UNSIGNED NOT NULL COMMENT '被申訴的對戰紀錄',
  `COMPLAINANT_MEM_ID` int(10) UNSIGNED NOT NULL COMMENT '提出申訴的會員編號',
  `RESPONDENT_MEM_ID` int(10) UNSIGNED NOT NULL COMMENT '被申訴會員編號',
  `APPEAL_CONTENT` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '申訴內容',
  `APPEAL_STATUS` enum('PENDING','CONFIRMED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING' COMMENT '申訴處理狀態',
  `ADMIN_ID` int(10) UNSIGNED DEFAULT NULL COMMENT '處理申訴的管理員編號',
  `CREATED_AT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '申訴提交時間',
  `RESPONDED_AT` datetime DEFAULT NULL COMMENT '管理員回覆時間',
  `PHOTO_EVIDENCE` text COLLATE utf8mb4_unicode_ci COMMENT '佐證截圖路徑JSON',
  `RESPONDED_TEXT` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '管理員回覆內容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='對戰申訴表';

-- --------------------------------------------------------

--
-- 資料表結構 `battle_manage_record`
--

CREATE TABLE `battle_manage_record` (
  `BATTLE_MANAGE_ID` int(10) UNSIGNED NOT NULL COMMENT '約戰管理處置編號',
  `BATTLE_ID` int(11) UNSIGNED NOT NULL COMMENT '被處置的約戰編號',
  `ADMIN_ID` int(10) UNSIGNED DEFAULT NULL COMMENT '執行處置的管理員編號',
  `MANAGE_ACTION` enum('REMOVE','RESTORE') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '管理處置：REMOVE下架、RESTORE恢復上架',
  `MANAGE_REASON` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '管理處置說明',
  `CREATED_AT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '處置時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='約戰管理處置紀錄表';

-- --------------------------------------------------------

--
-- 資料表結構 `battle_record`
--

CREATE TABLE `battle_record` (
  `BATTLE_ID` int(11) UNSIGNED NOT NULL COMMENT '對戰邀約編號',
  `INITIATOR_ID` int(10) UNSIGNED NOT NULL COMMENT '發起人會員編號',
  `PARTICIPANT_ID` int(10) UNSIGNED DEFAULT NULL COMMENT '參加人會員編號',
  `BATTLE_TITLE` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '約戰標題',
  `BATTLE_IMG` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'battle_card_default.jpg' COMMENT '約戰封面圖路徑',
  `BATTLE_DESC` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '約戰說明/備註',
  `CITY_ID` int(10) UNSIGNED NOT NULL COMMENT '縣市',
  `DISTRICT_ID` int(10) UNSIGNED NOT NULL COMMENT '行政區',
  `BATTLE_MODE` enum('COMPETITIVE','CASUAL') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '對戰模式',
  `BATTLE_TARGET` enum('ALL','ADULT','FAMILY') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '適合對象',
  `BATTLE_LEVEL` enum('ALL','BEGINNER','INTERMEDIATE','ADVANCED') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '適合玩家程度',
  `BATTLE_DATE` datetime NOT NULL COMMENT '約戰日期時間',
  `BATTLE_DEADLINE` datetime NOT NULL COMMENT '申請加入截止時間',
  `BATTLE_LOC` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '集合地點',
  `BATTLE_STATUS` enum('MATCHING','PENDING','CONFIRMED','FAILED','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MATCHING' COMMENT '約戰配對狀態',
  `INI_CONTACT` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '發起人公開聯絡資訊',
  `PAR_CONTACT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '參加人公開聯絡資訊',
  `WINNER` tinyint(4) DEFAULT NULL COMMENT '0發起人勝，1參加人勝',
  `TO_INI_STARS` tinyint(4) DEFAULT NULL COMMENT '對發起人的評價星等',
  `TO_PAR_STARS` tinyint(4) DEFAULT NULL COMMENT '對參加人的評價星等',
  `CREATED_AT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '邀約建立時間',
  `TO_INI_COMMENT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '對發起人的留言評論',
  `TO_INI_COMMENTED_AT` datetime DEFAULT NULL COMMENT '對發起人的留言時間',
  `TO_PAR_COMMENT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '對參加人的留言評論',
  `TO_PAR_COMMENTED_AT` datetime DEFAULT NULL COMMENT '對參加人的留言時間',
  `IS_SHOW` tinyint(1) NOT NULL DEFAULT '1' COMMENT '顯示狀態'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='對戰紀錄表';

--
-- 傾印資料表的資料 `battle_record`
--

INSERT INTO `battle_record` (`BATTLE_ID`, `INITIATOR_ID`, `PARTICIPANT_ID`, `BATTLE_TITLE`, `BATTLE_IMG`, `BATTLE_DESC`, `CITY_ID`, `DISTRICT_ID`, `BATTLE_MODE`, `BATTLE_TARGET`, `BATTLE_LEVEL`, `BATTLE_DATE`, `BATTLE_DEADLINE`, `BATTLE_LOC`, `BATTLE_STATUS`, `INI_CONTACT`, `PAR_CONTACT`, `WINNER`, `TO_INI_STARS`, `TO_PAR_STARS`, `CREATED_AT`, `TO_INI_COMMENT`, `TO_INI_COMMENTED_AT`, `TO_PAR_COMMENT`, `TO_PAR_COMMENTED_AT`, `IS_SHOW`) VALUES
(1, 1, NULL, '新手友善！開心打陀螺', 'battle_card_test1.jpg', '假日放鬆場，輕鬆交流，我帶戰鬥盤，你帶陀螺就好！', 3, 43, 'CASUAL', 'ALL', 'BEGINNER', '2026-09-05 14:00:00', '2026-09-04 22:00:00', '桃園市中壢區中壢火車站附近', 'MATCHING', 'LINE：weichen01', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(2, 2, NULL, '中壢車站週末交流場', 'battle_card_default.jpg', '歡迎各種程度的玩家參加，現場會準備戰鬥盤，一起輕鬆交流。', 3, 43, 'CASUAL', 'ALL', 'ALL', '2026-09-06 15:00:00', '2026-09-05 20:00:00', '桃園市中壢區中壢火車站前廣場', 'MATCHING', 'LINE：spinray02', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(3, 3, NULL, '競技模式實戰練習', 'battle_card_test2.jpg', '以競技規則進行實戰交流，適合已有對戰經驗並想增加實戰經驗的玩家。', 1, 3, 'COMPETITIVE', 'ADULT', 'ADVANCED', '2026-09-09 19:00:00', '2026-09-08 18:00:00', '臺北市大安區捷運忠孝復興站附近', 'MATCHING', 'LINE：bladegirl03', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(4, 4, NULL, '親子陀螺交流體驗', 'battle_card_default.jpg', '適合親子一起參加的輕鬆交流場，第一次接觸戰鬥陀螺也沒問題。', 2, 13, 'CASUAL', 'FAMILY', 'BEGINNER', '2026-09-12 10:30:00', '2026-09-11 18:00:00', '新北市板橋區板橋車站附近', 'MATCHING', 'LINE：beyrookie04', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(5, 5, NULL, '下班後來一場吧！', 'battle_card_default.jpg', '下班後簡單玩幾場，主要以輕鬆交流為主，地點鄰近捷運站。', 1, 2, 'CASUAL', 'ADULT', 'INTERMEDIATE', '2026-09-15 19:30:00', '2026-09-15 12:00:00', '臺北市信義區捷運市政府站附近', 'MATCHING', 'LINE：sunny05', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(6, 6, NULL, '進階玩家配置測試場', 'battle_card_default.jpg', '帶上最近調整的陀螺配置，一起測試不同零件搭配在實戰中的效果。', 2, 17, 'COMPETITIVE', 'ALL', 'ADVANCED', '2026-09-19 14:00:00', '2026-09-18 22:00:00', '新北市新莊區捷運新莊站附近', 'MATCHING', 'LINE：littleblade06', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(7, 7, NULL, '初次約戰也不用緊張', 'battle_card_default.jpg', '以交流和認識同好為主，不熟悉規則也可以放心參加，歡迎新手玩家。', 3, 45, 'CASUAL', 'ALL', 'BEGINNER', '2026-09-20 13:00:00', '2026-09-19 20:00:00', '桃園市八德區八德廣豐新天地附近', 'MATCHING', 'LINE：kenblade07', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(8, 9, NULL, '週日下午競技交流', 'battle_card_default.jpg', '依照競技規則進行多場實戰，歡迎想測試配置與累積實戰經驗的玩家。', 3, 44, 'COMPETITIVE', 'ADULT', 'INTERMEDIATE', '2026-09-26 15:00:00', '2026-09-25 23:00:00', '桃園市平鎮區新勢公園附近', 'MATCHING', 'LINE：beymom09', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(9, 10, NULL, '桃園陀螺玩家輕鬆聚', 'battle_card_default.jpg', '不論是收藏、配置分享或實際對戰都歡迎，一起認識附近的陀螺同好。', 3, 42, 'CASUAL', 'FAMILY', 'ALL', '2026-09-27 14:30:00', '2026-09-26 21:00:00', '桃園市桃園區桃園藝文特區附近', 'MATCHING', 'LINE：storm10', NULL, NULL, NULL, NULL, '2026-08-17 20:35:15', NULL, NULL, NULL, NULL, 1),
(10, 1, 2, '中壢週末輕鬆交流', 'battle_card_default.jpg', '假日下午簡單交流配置與玩法。', 3, 43, 'CASUAL', 'ALL', 'ALL', '2026-07-05 14:00:00', '2026-07-04 20:00:00', '桃園市中壢區中壢火車站附近', 'CONFIRMED', 'LINE：weichen01', 'LINE：spinray02', NULL, 5, 5, '2026-08-18 13:54:19', '準時又親切，場地資訊也說明得很清楚。', '2026-07-05 17:30:00', '交流過程很愉快，也分享了不少配置想法。', '2026-07-05 17:45:00', 1),
(11, 2, 3, '競技配置實戰測試', 'battle_card_default.jpg', '以競技規則測試近期調整的配置。', 1, 3, 'COMPETITIVE', 'ADULT', 'ADVANCED', '2026-07-10 19:00:00', '2026-07-09 20:00:00', '臺北市大安區忠孝復興站附近', 'CONFIRMED', 'LINE：spinray02', 'LINE：bladegirl03', 1, 4, 5, '2026-08-18 13:54:19', '對戰流程安排順暢，是很值得交流的玩家。', '2026-07-10 21:30:00', '實戰經驗豐富，對戰後也願意交流配置心得。', '2026-07-10 21:40:00', 1),
(12, 3, 4, '新手友善交流下午場', 'battle_card_default.jpg', '以交流為主，協助新手熟悉基本對戰方式。', 2, 13, 'CASUAL', 'ALL', 'BEGINNER', '2026-07-15 15:00:00', '2026-07-14 21:00:00', '新北市板橋區板橋車站附近', 'CONFIRMED', 'LINE：bladegirl03', 'LINE：beyrookie04', NULL, 5, 5, '2026-08-18 13:54:19', '很有耐心，對新手也會主動說明玩法。', '2026-07-15 18:00:00', '態度很好，交流過程也很有禮貌。', '2026-07-15 18:10:00', 1),
(13, 4, 5, '假日新手陀螺同樂會', 'battle_card_default.jpg', '新手玩家一起練習與認識不同配置。', 3, 42, 'CASUAL', 'FAMILY', 'BEGINNER', '2026-07-20 13:30:00', '2026-07-19 20:00:00', '桃園市桃園區藝文特區附近', 'CONFIRMED', 'LINE：beyrookie04', 'LINE：sunny05', NULL, 4, 5, '2026-08-18 13:54:19', '現場氣氛很輕鬆，適合剛開始玩的玩家。', '2026-07-20 16:30:00', '很有活力，也很願意和其他玩家交流。', '2026-07-20 16:40:00', 1),
(14, 5, 6, '少年玩家競技練習場', 'battle_card_default.jpg', '進行數場競技規則實戰練習。', 3, 43, 'COMPETITIVE', 'ALL', 'INTERMEDIATE', '2026-07-25 14:00:00', '2026-07-24 20:00:00', '桃園市中壢區中央公園附近', 'CONFIRMED', 'LINE：sunny05', 'LINE：littleblade06', 0, 5, 4, '2026-08-18 13:54:19', '準備充分，對戰時也很遵守規則。', '2026-07-25 17:00:00', '對戰態度認真，之後還會想再一起交流。', '2026-07-25 17:15:00', 1),
(15, 6, 7, '配置分享輕鬆交流場', 'battle_card_default.jpg', '分享近期喜歡的配置並進行友善交流。', 2, 17, 'CASUAL', 'ALL', 'ALL', '2026-07-30 15:00:00', '2026-07-29 21:00:00', '新北市新莊區捷運新莊站附近', 'CONFIRMED', 'LINE：littleblade06', 'LINE：kenblade07', NULL, 5, 5, '2026-08-18 13:54:19', '很友善，也會主動分享自己使用零件的心得。', '2026-07-30 18:20:00', '交流態度很好，是很有潛力的玩家。', '2026-07-30 18:30:00', 1),
(16, 7, 9, '週末競技實戰挑戰', 'battle_card_default.jpg', '以完整競技規則進行多場實戰。', 3, 44, 'COMPETITIVE', 'ADULT', 'ADVANCED', '2026-08-02 16:00:00', '2026-08-01 22:00:00', '桃園市平鎮區新勢公園附近', 'CONFIRMED', 'LINE：kenblade07', 'LINE：beymom09', 1, 5, 5, '2026-08-18 13:54:19', '實戰經驗非常豐富，對戰節奏也掌握得很好。', '2026-08-02 19:00:00', '對手很有實力，整場對戰十分精彩。', '2026-08-02 19:10:00', 1),
(17, 9, 10, '親子友善陀螺交流', 'battle_card_default.jpg', '親子與一般玩家都能輕鬆參加的交流場。', 3, 42, 'CASUAL', 'FAMILY', 'ALL', '2026-08-07 14:30:00', '2026-08-06 20:00:00', '桃園市桃園區藝文特區附近', 'CONFIRMED', 'LINE：beymom09', 'LINE：storm10', NULL, 5, 4, '2026-08-18 13:54:19', '活動安排很細心，對親子玩家也非常友善。', '2026-08-07 17:30:00', '交流過程很愉快，現場氣氛也很好。', '2026-08-07 17:45:00', 1),
(18, 10, 1, '進階玩家夜間競技戰', 'battle_card_default.jpg', '進階玩家測試配置與累積實戰經驗。', 1, 2, 'COMPETITIVE', 'ADULT', 'ADVANCED', '2026-08-12 19:30:00', '2026-08-11 22:00:00', '臺北市信義區市政府站附近', 'CONFIRMED', 'LINE：storm10', 'LINE：weichen01', 1, 4, 5, '2026-08-18 13:54:19', '對戰強度很高，也很願意分享自己的戰術思路。', '2026-08-12 22:00:00', '發起人很準時，整體對戰流程也很順暢。', '2026-08-12 22:10:00', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `beyblade`
--

CREATE TABLE `beyblade` (
  `beyblade_id` int(10) UNSIGNED NOT NULL COMMENT '編號',
  `category` enum('Blade','Ratchet','Bit') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '類別',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名稱',
  `attack` int(11) NOT NULL COMMENT '攻擊',
  `defense` int(11) NOT NULL COMMENT '防禦',
  `stamina` int(11) NOT NULL COMMENT '持久',
  `weight` int(11) NOT NULL COMMENT '重量',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '圖片',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上架狀態'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='陀螺零件表';

--
-- 傾印資料表的資料 `beyblade`
--

INSERT INTO `beyblade` (`beyblade_id`, `category`, `name`, `attack`, `defense`, `stamina`, `weight`, `pic`, `is_show`) VALUES
(1, 'Blade', '暴風天馬', 85, 40, 55, 32, 'build/blade/暴風天馬.png', 1),
(2, 'Blade', '蒼龍神劍', 78, 50, 60, 30, 'build/blade/蒼龍神劍.png', 1),
(3, 'Ratchet', 'BX-50-01', 45, 90, 65, 18, 'build/ratchet/BX-50-01.png', 1),
(4, 'Ratchet', 'CX-17-01', 40, 75, 95, 20, 'build/ratchet/CX-17-01.png', 0),
(5, 'Bit', 'UX-20', 30, 25, 50, 8, 'build/bit/UX-20.png', 1),
(6, 'Bit', 'CX-17-02', 20, 60, 80, 9, 'build/bit/CX-17-02.png', 0);

-- --------------------------------------------------------

--
-- 資料表結構 `city`
--

CREATE TABLE `city` (
  `CITY_ID` int(10) UNSIGNED NOT NULL COMMENT '縣市編號',
  `CITY_NAME` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '縣市名稱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='縣市資料表';

--
-- 傾印資料表的資料 `city`
--

INSERT INTO `city` (`CITY_ID`, `CITY_NAME`) VALUES
(13, '南投縣'),
(9, '嘉義市'),
(15, '嘉義縣'),
(7, '基隆市'),
(17, '宜蘭縣'),
(16, '屏東縣'),
(12, '彰化縣'),
(2, '新北市'),
(8, '新竹市'),
(10, '新竹縣'),
(3, '桃園市'),
(20, '澎湖縣'),
(4, '臺中市'),
(1, '臺北市'),
(5, '臺南市'),
(19, '臺東縣'),
(18, '花蓮縣'),
(11, '苗栗縣'),
(22, '連江縣'),
(21, '金門縣'),
(14, '雲林縣'),
(6, '高雄市');

-- --------------------------------------------------------

--
-- 資料表結構 `district`
--

CREATE TABLE `district` (
  `DISTRICT_ID` int(10) UNSIGNED NOT NULL COMMENT '行政區編號',
  `CITY_ID` int(10) UNSIGNED NOT NULL COMMENT '所屬縣市編號',
  `DISTRICT_NAME` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '行政區名稱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='行政區資料表';

--
-- 傾印資料表的資料 `district`
--

INSERT INTO `district` (`DISTRICT_ID`, `CITY_ID`, `DISTRICT_NAME`) VALUES
(1, 1, '松山區'),
(2, 1, '信義區'),
(3, 1, '大安區'),
(4, 1, '中山區'),
(5, 1, '中正區'),
(6, 1, '大同區'),
(7, 1, '萬華區'),
(8, 1, '文山區'),
(9, 1, '南港區'),
(10, 1, '內湖區'),
(11, 1, '士林區'),
(12, 1, '北投區'),
(13, 2, '板橋區'),
(14, 2, '三重區'),
(15, 2, '中和區'),
(16, 2, '永和區'),
(17, 2, '新莊區'),
(18, 2, '新店區'),
(19, 2, '樹林區'),
(20, 2, '鶯歌區'),
(21, 2, '三峽區'),
(22, 2, '淡水區'),
(23, 2, '汐止區'),
(24, 2, '瑞芳區'),
(25, 2, '土城區'),
(26, 2, '蘆洲區'),
(27, 2, '五股區'),
(28, 2, '泰山區'),
(29, 2, '林口區'),
(30, 2, '深坑區'),
(31, 2, '石碇區'),
(32, 2, '坪林區'),
(33, 2, '三芝區'),
(34, 2, '石門區'),
(35, 2, '八里區'),
(36, 2, '平溪區'),
(37, 2, '雙溪區'),
(38, 2, '貢寮區'),
(39, 2, '金山區'),
(40, 2, '萬里區'),
(41, 2, '烏來區'),
(42, 3, '桃園區'),
(43, 3, '中壢區'),
(44, 3, '平鎮區'),
(45, 3, '八德區'),
(46, 3, '楊梅區'),
(47, 3, '蘆竹區'),
(48, 3, '大溪區'),
(49, 3, '龍潭區'),
(50, 3, '龜山區'),
(51, 3, '大園區'),
(52, 3, '觀音區'),
(53, 3, '新屋區'),
(54, 3, '復興區'),
(55, 4, '中區'),
(56, 4, '東區'),
(57, 4, '南區'),
(58, 4, '西區'),
(59, 4, '北區'),
(60, 4, '北屯區'),
(61, 4, '西屯區'),
(62, 4, '南屯區'),
(63, 4, '太平區'),
(64, 4, '大里區'),
(65, 4, '霧峰區'),
(66, 4, '烏日區'),
(67, 4, '豐原區'),
(68, 4, '后里區'),
(69, 4, '石岡區'),
(70, 4, '東勢區'),
(71, 4, '和平區'),
(72, 4, '新社區'),
(73, 4, '潭子區'),
(74, 4, '大雅區'),
(75, 4, '神岡區'),
(76, 4, '大肚區'),
(77, 4, '沙鹿區'),
(78, 4, '龍井區'),
(79, 4, '梧棲區'),
(80, 4, '清水區'),
(81, 4, '大甲區'),
(82, 4, '外埔區'),
(83, 4, '大安區'),
(84, 5, '中西區'),
(85, 5, '東區'),
(86, 5, '南區'),
(87, 5, '北區'),
(88, 5, '安平區'),
(89, 5, '安南區'),
(90, 5, '永康區'),
(91, 5, '歸仁區'),
(92, 5, '新化區'),
(93, 5, '左鎮區'),
(94, 5, '玉井區'),
(95, 5, '楠西區'),
(96, 5, '南化區'),
(97, 5, '仁德區'),
(98, 5, '關廟區'),
(99, 5, '龍崎區'),
(100, 5, '官田區'),
(101, 5, '麻豆區'),
(102, 5, '佳里區'),
(103, 5, '西港區'),
(104, 5, '七股區'),
(105, 5, '將軍區'),
(106, 5, '學甲區'),
(107, 5, '北門區'),
(108, 5, '新營區'),
(109, 5, '後壁區'),
(110, 5, '白河區'),
(111, 5, '東山區'),
(112, 5, '六甲區'),
(113, 5, '下營區'),
(114, 5, '柳營區'),
(115, 5, '鹽水區'),
(116, 5, '善化區'),
(117, 5, '大內區'),
(118, 5, '山上區'),
(119, 5, '新市區'),
(120, 5, '安定區'),
(121, 6, '新興區'),
(122, 6, '前金區'),
(123, 6, '苓雅區'),
(124, 6, '鹽埕區'),
(125, 6, '鼓山區'),
(126, 6, '旗津區'),
(127, 6, '前鎮區'),
(128, 6, '三民區'),
(129, 6, '楠梓區'),
(130, 6, '小港區'),
(131, 6, '左營區'),
(132, 6, '仁武區'),
(133, 6, '大社區'),
(134, 6, '岡山區'),
(135, 6, '路竹區'),
(136, 6, '阿蓮區'),
(137, 6, '田寮區'),
(138, 6, '燕巢區'),
(139, 6, '橋頭區'),
(140, 6, '梓官區'),
(141, 6, '彌陀區'),
(142, 6, '永安區'),
(143, 6, '湖內區'),
(144, 6, '鳳山區'),
(145, 6, '大寮區'),
(146, 6, '林園區'),
(147, 6, '鳥松區'),
(148, 6, '大樹區'),
(149, 6, '旗山區'),
(150, 6, '美濃區'),
(151, 6, '六龜區'),
(152, 6, '內門區'),
(153, 6, '杉林區'),
(154, 6, '甲仙區'),
(155, 6, '桃源區'),
(156, 6, '那瑪夏區'),
(157, 6, '茂林區'),
(158, 6, '茄萣區'),
(159, 7, '仁愛區'),
(160, 7, '信義區'),
(161, 7, '中正區'),
(162, 7, '中山區'),
(163, 7, '安樂區'),
(164, 7, '暖暖區'),
(165, 7, '七堵區'),
(166, 8, '東區'),
(167, 8, '北區'),
(168, 8, '香山區'),
(169, 9, '東區'),
(170, 9, '西區'),
(171, 10, '竹北市'),
(172, 10, '竹東鎮'),
(173, 10, '新埔鎮'),
(174, 10, '關西鎮'),
(175, 10, '湖口鄉'),
(176, 10, '新豐鄉'),
(177, 10, '芎林鄉'),
(178, 10, '橫山鄉'),
(179, 10, '北埔鄉'),
(180, 10, '寶山鄉'),
(181, 10, '峨眉鄉'),
(182, 10, '尖石鄉'),
(183, 10, '五峰鄉'),
(184, 11, '苗栗市'),
(185, 11, '頭份市'),
(186, 11, '竹南鎮'),
(187, 11, '後龍鎮'),
(188, 11, '通霄鎮'),
(189, 11, '苑裡鎮'),
(190, 11, '卓蘭鎮'),
(191, 11, '造橋鄉'),
(192, 11, '西湖鄉'),
(193, 11, '頭屋鄉'),
(194, 11, '公館鄉'),
(195, 11, '銅鑼鄉'),
(196, 11, '三義鄉'),
(197, 11, '大湖鄉'),
(198, 11, '獅潭鄉'),
(199, 11, '三灣鄉'),
(200, 11, '南庄鄉'),
(201, 11, '泰安鄉'),
(202, 12, '彰化市'),
(203, 12, '員林市'),
(204, 12, '和美鎮'),
(205, 12, '鹿港鎮'),
(206, 12, '溪湖鎮'),
(207, 12, '二林鎮'),
(208, 12, '田中鎮'),
(209, 12, '北斗鎮'),
(210, 12, '花壇鄉'),
(211, 12, '芬園鄉'),
(212, 12, '大村鄉'),
(213, 12, '永靖鄉'),
(214, 12, '伸港鄉'),
(215, 12, '線西鄉'),
(216, 12, '福興鄉'),
(217, 12, '秀水鄉'),
(218, 12, '埔心鄉'),
(219, 12, '埔鹽鄉'),
(220, 12, '大城鄉'),
(221, 12, '芳苑鄉'),
(222, 12, '竹塘鄉'),
(223, 12, '社頭鄉'),
(224, 12, '二水鄉'),
(225, 12, '田尾鄉'),
(226, 12, '埤頭鄉'),
(227, 12, '溪州鄉'),
(228, 13, '南投市'),
(229, 13, '埔里鎮'),
(230, 13, '草屯鎮'),
(231, 13, '竹山鎮'),
(232, 13, '集集鎮'),
(233, 13, '名間鄉'),
(234, 13, '鹿谷鄉'),
(235, 13, '中寮鄉'),
(236, 13, '魚池鄉'),
(237, 13, '國姓鄉'),
(238, 13, '水里鄉'),
(239, 13, '信義鄉'),
(240, 13, '仁愛鄉'),
(241, 14, '斗六市'),
(242, 14, '斗南鎮'),
(243, 14, '虎尾鎮'),
(244, 14, '西螺鎮'),
(245, 14, '土庫鎮'),
(246, 14, '北港鎮'),
(247, 14, '古坑鄉'),
(248, 14, '大埤鄉'),
(249, 14, '莿桐鄉'),
(250, 14, '林內鄉'),
(251, 14, '二崙鄉'),
(252, 14, '崙背鄉'),
(253, 14, '麥寮鄉'),
(254, 14, '東勢鄉'),
(255, 14, '褒忠鄉'),
(256, 14, '臺西鄉'),
(257, 14, '元長鄉'),
(258, 14, '四湖鄉'),
(259, 14, '口湖鄉'),
(260, 14, '水林鄉'),
(261, 15, '太保市'),
(262, 15, '朴子市'),
(263, 15, '布袋鎮'),
(264, 15, '大林鎮'),
(265, 15, '民雄鄉'),
(266, 15, '溪口鄉'),
(267, 15, '新港鄉'),
(268, 15, '六腳鄉'),
(269, 15, '東石鄉'),
(270, 15, '義竹鄉'),
(271, 15, '鹿草鄉'),
(272, 15, '水上鄉'),
(273, 15, '中埔鄉'),
(274, 15, '竹崎鄉'),
(275, 15, '梅山鄉'),
(276, 15, '番路鄉'),
(277, 15, '大埔鄉'),
(278, 15, '阿里山鄉'),
(279, 16, '屏東市'),
(280, 16, '潮州鎮'),
(281, 16, '東港鎮'),
(282, 16, '恆春鎮'),
(283, 16, '萬丹鄉'),
(284, 16, '長治鄉'),
(285, 16, '麟洛鄉'),
(286, 16, '九如鄉'),
(287, 16, '里港鄉'),
(288, 16, '鹽埔鄉'),
(289, 16, '高樹鄉'),
(290, 16, '萬巒鄉'),
(291, 16, '內埔鄉'),
(292, 16, '竹田鄉'),
(293, 16, '新埤鄉'),
(294, 16, '枋寮鄉'),
(295, 16, '新園鄉'),
(296, 16, '崁頂鄉'),
(297, 16, '林邊鄉'),
(298, 16, '南州鄉'),
(299, 16, '佳冬鄉'),
(300, 16, '琉球鄉'),
(301, 16, '車城鄉'),
(302, 16, '滿州鄉'),
(303, 16, '枋山鄉'),
(304, 16, '三地門鄉'),
(305, 16, '霧臺鄉'),
(306, 16, '瑪家鄉'),
(307, 16, '泰武鄉'),
(308, 16, '來義鄉'),
(309, 16, '春日鄉'),
(310, 16, '獅子鄉'),
(311, 16, '牡丹鄉'),
(312, 17, '宜蘭市'),
(313, 17, '羅東鎮'),
(314, 17, '蘇澳鎮'),
(315, 17, '頭城鎮'),
(316, 17, '礁溪鄉'),
(317, 17, '壯圍鄉'),
(318, 17, '員山鄉'),
(319, 17, '冬山鄉'),
(320, 17, '五結鄉'),
(321, 17, '三星鄉'),
(322, 17, '大同鄉'),
(323, 17, '南澳鄉'),
(324, 18, '花蓮市'),
(325, 18, '鳳林鎮'),
(326, 18, '玉里鎮'),
(327, 18, '新城鄉'),
(328, 18, '吉安鄉'),
(329, 18, '壽豐鄉'),
(330, 18, '光復鄉'),
(331, 18, '豐濱鄉'),
(332, 18, '瑞穗鄉'),
(333, 18, '富里鄉'),
(334, 18, '秀林鄉'),
(335, 18, '萬榮鄉'),
(336, 18, '卓溪鄉'),
(337, 19, '臺東市'),
(338, 19, '成功鎮'),
(339, 19, '關山鎮'),
(340, 19, '卑南鄉'),
(341, 19, '鹿野鄉'),
(342, 19, '池上鄉'),
(343, 19, '東河鄉'),
(344, 19, '長濱鄉'),
(345, 19, '太麻里鄉'),
(346, 19, '大武鄉'),
(347, 19, '綠島鄉'),
(348, 19, '海端鄉'),
(349, 19, '延平鄉'),
(350, 19, '金峰鄉'),
(351, 19, '達仁鄉'),
(352, 19, '蘭嶼鄉'),
(353, 20, '馬公市'),
(354, 20, '湖西鄉'),
(355, 20, '白沙鄉'),
(356, 20, '西嶼鄉'),
(357, 20, '望安鄉'),
(358, 20, '七美鄉'),
(359, 21, '金城鎮'),
(360, 21, '金湖鎮'),
(361, 21, '金沙鎮'),
(362, 21, '金寧鄉'),
(363, 21, '烈嶼鄉'),
(364, 21, '烏坵鄉'),
(365, 22, '南竿鄉'),
(366, 22, '北竿鄉'),
(367, 22, '莒光鄉'),
(368, 22, '東引鄉');

-- --------------------------------------------------------

--
-- 資料表結構 `exchange_comment`
--

CREATE TABLE `exchange_comment` (
  `comm_id` int(10) UNSIGNED NOT NULL COMMENT '留言ID',
  `post_id` int(11) NOT NULL COMMENT '回覆交換案件ID',
  `mem_id` int(10) UNSIGNED NOT NULL COMMENT '留言會員編號',
  `content` text NOT NULL COMMENT '回覆內容',
  `create_time` datetime NOT NULL COMMENT '回復時間',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否顯示',
  `remove_reason` varchar(255) DEFAULT NULL COMMENT '被下架時，下架原因說明',
  `is_choose` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被選擇',
  `comm_contact` varchar(50) NOT NULL COMMENT '申請交換人的聯絡資訊'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='二手交換-申請';

-- --------------------------------------------------------

--
-- 資料表結構 `exchange_post`
--

CREATE TABLE `exchange_post` (
  `post_id` int(11) NOT NULL COMMENT '交換案件編號',
  `type` enum('beyblade','blade','ratchet','bit','others') NOT NULL COMMENT '物品類別',
  `title` varchar(20) NOT NULL COMMENT '貼文標題',
  `description` mediumtext NOT NULL COMMENT '物品描述',
  `want_item` varchar(100) DEFAULT NULL COMMENT '希望獲得物品',
  `condition` enum('new','good','fair') NOT NULL COMMENT '商品狀況',
  `status` enum('available','exchanging','pending','completed') NOT NULL DEFAULT 'available' COMMENT '商品交換狀態',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否上架',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
  `post_pic1` varchar(255) NOT NULL COMMENT '物品圖片1',
  `post_pic2` varchar(255) DEFAULT NULL COMMENT '物品圖片2',
  `post_pic3` varchar(255) DEFAULT NULL COMMENT '物品圖片3',
  `post_pic4` varchar(255) DEFAULT NULL COMMENT '物品圖片4',
  `post_pic5` varchar(255) DEFAULT NULL COMMENT '物品圖片5',
  `remove_reason` varchar(255) DEFAULT NULL COMMENT '如果被下架時，下架原因說明',
  `mem_id` int(11) UNSIGNED NOT NULL COMMENT '發文會員編號',
  `comm_id` int(11) UNSIGNED DEFAULT NULL COMMENT '選中的申請交換留言ID',
  `CITY_ID` int(10) UNSIGNED NOT NULL COMMENT '縣市',
  `DISTRICT_ID` int(10) UNSIGNED NOT NULL COMMENT '行政區',
  `post_contact` varchar(50) NOT NULL COMMENT '發起交換人的聯絡資訊'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `exchange_post`
--

INSERT INTO `exchange_post` (`post_id`, `type`, `title`, `description`, `want_item`, `condition`, `status`, `is_show`, `create_time`, `post_pic1`, `post_pic2`, `post_pic3`, `post_pic4`, `post_pic5`, `remove_reason`, `mem_id`, `comm_id`, `CITY_ID`, `DISTRICT_ID`, `post_contact`) VALUES
(11, 'beyblade', 'CX 系列 對戰陀螺 改裝版', '關於交換戰鬥陀螺這件事，我們都可能想錯了，交換戰鬥陀螺，是很多人每天都在做的事，但真正把它做出意義的人，寥寥可數。 當我們認真審視交換戰鬥陀螺這件事，會發現它所牽涉的，遠比表面看來複雜得多。', '絕版陀螺12345', 'good', 'available', 1, '2026-08-26 10:51:34', 'uploads/articles/exchange_6d60773583af0e1b.png', 'uploads/articles/exchange_daa5721a23b843ba.png', 'uploads/articles/exchange_6ea78e51a93aaa73.png', 'uploads/articles/exchange_09f56c55636ebdb7.png', 'uploads/articles/exchange_2a2964e577c87845.png', NULL, 1, NULL, 1, 1, '電話:090909090');

-- --------------------------------------------------------

--
-- 資料表結構 `member`
--

CREATE TABLE `member` (
  `MEM_ID` int(10) UNSIGNED NOT NULL COMMENT '會員編號',
  `MEM_ACCOUNT` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '會員帳號（Email）',
  `MEM_PASSWORD` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '會員密碼',
  `MEM_NAME` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '會員姓名',
  `MEM_GENDER` enum('MALE','FEMALE') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '會員性別',
  `MEM_BIRTH` date NOT NULL COMMENT '會員生日',
  `REP_NAME` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '法定代理人姓名',
  `REP_RELATION` enum('FATHER','MOTHER','OTHER') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '法定代理人關係',
  `REP_PHONE` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '法定代理人電話',
  `MEM_PHOTO` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'spinix_member_default.png' COMMENT '會員頭像',
  `RATING_STARS` int(11) NOT NULL DEFAULT '0' COMMENT '累積評價星數',
  `RATING_COUNT` int(11) NOT NULL DEFAULT '0' COMMENT '評價總次數',
  `BATTLE_COUNT` int(11) NOT NULL DEFAULT '0' COMMENT '約戰總場次',
  `BATTLE_WINS` int(11) NOT NULL DEFAULT '0' COMMENT '約戰勝場數',
  `BATTLE_STATUS` enum('ACTIVE','TEMP-RESTRICT','PERMA-RESTRICT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE' COMMENT '約戰功能狀態',
  `BATTLE_SUSPEND_UNTIL` datetime DEFAULT NULL COMMENT '約戰功能受限到期時間',
  `FORUM_STATUS` enum('ACTIVE','TEMP-RESTRICT','PERMA-RESTRICT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE' COMMENT '論壇功能狀態',
  `FORUM_SUSPEND_UNTIL` datetime DEFAULT NULL COMMENT '論壇功能受限到期時間',
  `MARKET_STATUS` enum('ACTIVE','TEMP-RESTRICT','PERMA-RESTRICT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE' COMMENT '交換功能狀態',
  `MARKET_SUSPEND_UNTIL` datetime DEFAULT NULL COMMENT '交換功能受限到期時間',
  `BATTLE_VIO_COUNTS` int(11) NOT NULL DEFAULT '0' COMMENT '約戰違規次數',
  `EXCHANGE_VIO_COUNTS` int(11) NOT NULL DEFAULT '0' COMMENT '交換違規次數',
  `FORUM_VIO_COUNTS` int(11) NOT NULL DEFAULT '0' COMMENT '論壇違規次數'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='會員資料表';

--
-- 傾印資料表的資料 `member`
--

INSERT INTO `member` (`MEM_ID`, `MEM_ACCOUNT`, `MEM_PASSWORD`, `MEM_NAME`, `MEM_GENDER`, `MEM_BIRTH`, `REP_NAME`, `REP_RELATION`, `REP_PHONE`, `MEM_PHOTO`, `RATING_STARS`, `RATING_COUNT`, `BATTLE_COUNT`, `BATTLE_WINS`, `BATTLE_STATUS`, `BATTLE_SUSPEND_UNTIL`, `FORUM_STATUS`, `FORUM_SUSPEND_UNTIL`, `MARKET_STATUS`, `MARKET_SUSPEND_UNTIL`, `BATTLE_VIO_COUNTS`, `EXCHANGE_VIO_COUNTS`, `FORUM_VIO_COUNTS`) VALUES
(1, 'weichen01@spinix.test', '$2y$10$36FXbNX9.OcF8/ZdlyZFHeoZFo.cfQ4xiY.7o0V2EAIDRZbUhPzO6', 'WeiChen', 'MALE', '1998-05-16', NULL, NULL, NULL, 'spinix_member_test1.png', 47, 10, 18, 11, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 0),
(2, 'spinray02@spinix.test', '$2y$10$0UbuIUA1iHTGFap9TT0x8.Gu93SZ8U3NXsQigPHtHodjE/ZMqrwO.', 'SpinRay', 'MALE', '2001-11-03', NULL, NULL, NULL, 'spinix_member_test2.jpg', 42, 9, 15, 8, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 0),
(3, 'bladegirl03@spinix.test', '$2y$10$g.BUbBvJ2nkSy3XIh1vX5.g8BysCn0Dy7CM4nfFHjoCjDMS0w5UXe', 'Mika', 'FEMALE', '2000-03-21', NULL, NULL, NULL, 'spinix_member_test3.png', 34, 8, 12, 5, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 0),
(4, 'beyrookie04@spinix.test', '$2y$10$SloHzf2NCjlg7/zgRA3LfOM6XTtVYaSUhctZJlgRluXvxbAj0jA7W', '陀螺新手', 'MALE', '2005-09-12', NULL, NULL, NULL, 'spinix_member_default.png', 18, 4, 6, 2, 'ACTIVE', NULL, 'ACTIVE', NULL, 'TEMP-RESTRICT', '2026-08-31 23:59:59', 0, 0, 0),
(5, 'sunny05@spinix.test', '$2y$10$2XA3G48M3PHmfRI9cha4iuDhnrVS.AsXuUl.b.S3w1er93ovuvW3K', 'SunnyBlader', 'MALE', '2011-02-08', '王志明', 'FATHER', '0912345678', 'spinix_member_default.png', 24, 5, 8, 4, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 0),
(6, 'littleblade06@spinix.test', '$2y$10$i5SA2xDY98cjPXQh9Jv6AOZbJnii3Uz/TQ.9aMyqEiWGkdi5mOEqu', '小宇', 'MALE', '2012-07-19', '陳雅婷', 'MOTHER', '0922333444', 'spinix_member_default.png', 15, 3, 5, 1, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 0),
(7, 'kenblade07@spinix.test', '$2y$10$TKepVBeKc810vOnkvfyWtug.sdpsQd6rrv1r/LW2IQT2LiJtopwm2', 'BladeKen', 'MALE', '1996-12-01', NULL, NULL, NULL, 'spinix_member_default.png', 55, 12, 22, 14, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 1, 0, 0),
(8, 'rayblade08@spinix.test', '$2y$10$GaXeLSqIBz5g9juuvhBxnuzACafuQCnrof3WqLcQ52OT28HeG07Di', 'Ray', 'MALE', '1999-08-27', NULL, NULL, NULL, 'spinix_member_default.png', 29, 7, 10, 6, 'TEMP-RESTRICT', '2026-08-31 23:59:59', 'ACTIVE', NULL, 'ACTIVE', NULL, 2, 0, 0),
(9, 'beymom09@spinix.test', '$2y$10$9AwGIs7nGtQoN6tbMTSwQuqKcD.CplJR/CvPytUovtjk1vP2rMPV2', '旋風媽咪', 'FEMALE', '1987-04-11', NULL, NULL, NULL, 'spinix_member_default.png', 39, 8, 14, 7, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 1),
(10, 'storm10@spinix.test', '$2y$10$jF5gOcAZh7Xzj1/sIRyT6uz4RMiUDzaVoj7F6sxNEEbHHx0wjtUwi', 'StormLeo', 'MALE', '1994-06-30', NULL, NULL, NULL, 'spinix_member_default.png', 61, 13, 25, 17, 'ACTIVE', NULL, 'TEMP-RESTRICT', '2026-08-31 23:59:59', 'ACTIVE', NULL, 0, 0, 2),
(11, 'testmember11@spinix.test', '$2y$10$2VtHjYLe/BuWFYXfXHWTSunFZaqzu/WiQKbKzRK1T22/Z2nSQcTLa', '測試員大大', 'MALE', '2000-01-01', NULL, NULL, NULL, 'spinix_member_default.png', 0, 0, 0, 0, 'ACTIVE', NULL, 'ACTIVE', NULL, 'ACTIVE', NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `message`
--

CREATE TABLE `message` (
  `msg_id` int(10) UNSIGNED NOT NULL COMMENT '留言編號',
  `mem_id` int(10) UNSIGNED NOT NULL COMMENT '會員ID',
  `art_id` int(10) UNSIGNED NOT NULL COMMENT '貼文編號',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '留言內容',
  `create_time` datetime NOT NULL COMMENT '留言時間',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '顯示狀態',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '圖片'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='論壇留言表';

--
-- 傾印資料表的資料 `message`
--

INSERT INTO `message` (`msg_id`, `mem_id`, `art_id`, `content`, `create_time`, `is_show`, `pic`) VALUES
(1, 2, 1, '推！這個規章講解得很清楚', '2026-08-01 10:30:00', 1, NULL),
(2, 5, 2, '這顆真的稀有，羨慕！請問哪裡買的到類似的嗎', '2026-08-05 15:00:00', 1, 'message_pic_02.jpg'),
(3, 1, 3, '請問交通接駁車幾點發車呢？', '2026-08-08 11:20:00', 1, NULL),
(4, 8, 4, '我都用軟布沾一點潤滑油輕輕擦拭', '2026-08-10 21:00:00', 1, NULL),
(5, 9, 5, '這種說法太片面了吧，內容根本亂寫', '2026-08-13 08:10:00', 0, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `notification`
--

CREATE TABLE `notification` (
  `ntfn_id` int(10) UNSIGNED NOT NULL COMMENT '通知ID',
  `mem_id` int(10) UNSIGNED NOT NULL COMMENT '會員編號',
  `content` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通知內容',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已讀',
  `create_time` datetime NOT NULL COMMENT '通知時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='會員通知';

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uk_admin_account` (`account`),
  ADD UNIQUE KEY `uk_admin_name` (`name`);

--
-- 資料表索引 `appeal_exchange`
--
ALTER TABLE `appeal_exchange`
  ADD PRIMARY KEY (`ae_id`),
  ADD KEY `fk_appeal_exchange_post` (`post_id`),
  ADD KEY `fk_appeal_exchange_comm` (`comm_id`),
  ADD KEY `fk_appeal_exchange_complainant` (`complainant_mem_id`),
  ADD KEY `fk_appeal_exchange_respondent` (`respondent_mem_id`),
  ADD KEY `fk_appeal_exchange_admin` (`admin_id`);

--
-- 資料表索引 `appeal_forum`
--
ALTER TABLE `appeal_forum`
  ADD PRIMARY KEY (`af_id`),
  ADD KEY `fk_appeal_forum_article` (`art_id`),
  ADD KEY `fk_appeal_forum_message` (`msg_id`),
  ADD KEY `fk_appeal_forum_complainant` (`complainant_mem_id`),
  ADD KEY `fk_appeal_forum_respondent` (`respondent_mem_id`),
  ADD KEY `fk_appeal_forum_admin` (`admin_id`);

--
-- 資料表索引 `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`art_id`),
  ADD KEY `fk_article_member` (`mem_id`);

--
-- 資料表索引 `battle_appeal`
--
ALTER TABLE `battle_appeal`
  ADD PRIMARY KEY (`BATTLE_APPEAL_ID`),
  ADD KEY `FK_BATTLE_APPEAL_BATTLE` (`BATTLE_ID`),
  ADD KEY `FK_BATTLE_APPEAL_COMPLAINANT` (`COMPLAINANT_MEM_ID`),
  ADD KEY `FK_BATTLE_APPEAL_RESPONDENT` (`RESPONDENT_MEM_ID`),
  ADD KEY `FK_BATTLE_APPEAL_ADMIN` (`ADMIN_ID`);

--
-- 資料表索引 `battle_manage_record`
--
ALTER TABLE `battle_manage_record`
  ADD PRIMARY KEY (`BATTLE_MANAGE_ID`),
  ADD KEY `FK_BATTLE_MANAGE_BATTLE` (`BATTLE_ID`),
  ADD KEY `FK_BATTLE_MANAGE_ADMIN` (`ADMIN_ID`);

--
-- 資料表索引 `battle_record`
--
ALTER TABLE `battle_record`
  ADD PRIMARY KEY (`BATTLE_ID`),
  ADD KEY `FK_BATTLE_INITIATOR` (`INITIATOR_ID`),
  ADD KEY `FK_BATTLE_PARTICIPANT` (`PARTICIPANT_ID`),
  ADD KEY `FK_BATTLE_CITY` (`CITY_ID`),
  ADD KEY `FK_BATTLE_DISTRICT` (`DISTRICT_ID`),
  ADD KEY `IDX_BATTLE_STATUS_DATE` (`BATTLE_STATUS`,`BATTLE_DATE`);

--
-- 資料表索引 `beyblade`
--
ALTER TABLE `beyblade`
  ADD PRIMARY KEY (`beyblade_id`);

--
-- 資料表索引 `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`CITY_ID`),
  ADD UNIQUE KEY `UQ_CITY_NAME` (`CITY_NAME`);

--
-- 資料表索引 `district`
--
ALTER TABLE `district`
  ADD PRIMARY KEY (`DISTRICT_ID`),
  ADD KEY `IDX_DISTRICT_CITY_ID` (`CITY_ID`);

--
-- 資料表索引 `exchange_comment`
--
ALTER TABLE `exchange_comment`
  ADD PRIMARY KEY (`comm_id`),
  ADD KEY `fk_exchange_comment_post` (`post_id`),
  ADD KEY `fk_exchange_comment_member` (`mem_id`);

--
-- 資料表索引 `exchange_post`
--
ALTER TABLE `exchange_post`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `CITY_ID` (`CITY_ID`),
  ADD KEY `DISTRICT_ID` (`DISTRICT_ID`),
  ADD KEY `mem_id` (`mem_id`),
  ADD KEY `comm_id` (`comm_id`) USING BTREE;

--
-- 資料表索引 `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MEM_ID`),
  ADD UNIQUE KEY `UQ_MEMBER_ACCOUNT` (`MEM_ACCOUNT`);

--
-- 資料表索引 `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `fk_message_member` (`mem_id`),
  ADD KEY `fk_message_article` (`art_id`);

--
-- 資料表索引 `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`ntfn_id`),
  ADD KEY `fk_notification_member` (`mem_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理員ID', AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `appeal_exchange`
--
ALTER TABLE `appeal_exchange`
  MODIFY `ae_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '交換申訴ID', AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `appeal_forum`
--
ALTER TABLE `appeal_forum`
  MODIFY `af_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '貼文申訴ID', AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `article`
--
ALTER TABLE `article`
  MODIFY `art_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '貼文編號', AUTO_INCREMENT=7;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `battle_appeal`
--
ALTER TABLE `battle_appeal`
  MODIFY `BATTLE_APPEAL_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '對戰申訴編號';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `battle_manage_record`
--
ALTER TABLE `battle_manage_record`
  MODIFY `BATTLE_MANAGE_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '約戰管理處置編號';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `battle_record`
--
ALTER TABLE `battle_record`
  MODIFY `BATTLE_ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '對戰邀約編號', AUTO_INCREMENT=19;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `beyblade`
--
ALTER TABLE `beyblade`
  MODIFY `beyblade_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '編號', AUTO_INCREMENT=7;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `city`
--
ALTER TABLE `city`
  MODIFY `CITY_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '縣市編號', AUTO_INCREMENT=23;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `district`
--
ALTER TABLE `district`
  MODIFY `DISTRICT_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '行政區編號', AUTO_INCREMENT=369;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `exchange_comment`
--
ALTER TABLE `exchange_comment`
  MODIFY `comm_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '留言ID', AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `exchange_post`
--
ALTER TABLE `exchange_post`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '交換案件編號', AUTO_INCREMENT=12;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `member`
--
ALTER TABLE `member`
  MODIFY `MEM_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '會員編號', AUTO_INCREMENT=12;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `message`
--
ALTER TABLE `message`
  MODIFY `msg_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '留言編號', AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `notification`
--
ALTER TABLE `notification`
  MODIFY `ntfn_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通知ID';

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `appeal_exchange`
--
ALTER TABLE `appeal_exchange`
  ADD CONSTRAINT `fk_appeal_exchange_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `fk_appeal_exchange_comm` FOREIGN KEY (`comm_id`) REFERENCES `exchange_comment` (`comm_id`),
  ADD CONSTRAINT `fk_appeal_exchange_complainant` FOREIGN KEY (`complainant_mem_id`) REFERENCES `member` (`MEM_ID`),
  ADD CONSTRAINT `fk_appeal_exchange_post` FOREIGN KEY (`post_id`) REFERENCES `exchange_post` (`post_id`),
  ADD CONSTRAINT `fk_appeal_exchange_respondent` FOREIGN KEY (`respondent_mem_id`) REFERENCES `member` (`MEM_ID`);

--
-- 資料表的限制式 `appeal_forum`
--
ALTER TABLE `appeal_forum`
  ADD CONSTRAINT `fk_appeal_forum_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `fk_appeal_forum_article` FOREIGN KEY (`art_id`) REFERENCES `article` (`art_id`),
  ADD CONSTRAINT `fk_appeal_forum_complainant` FOREIGN KEY (`complainant_mem_id`) REFERENCES `member` (`MEM_ID`),
  ADD CONSTRAINT `fk_appeal_forum_message` FOREIGN KEY (`msg_id`) REFERENCES `message` (`msg_id`),
  ADD CONSTRAINT `fk_appeal_forum_respondent` FOREIGN KEY (`respondent_mem_id`) REFERENCES `member` (`MEM_ID`);

--
-- 資料表的限制式 `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `fk_article_member` FOREIGN KEY (`mem_id`) REFERENCES `member` (`MEM_ID`);

--
-- 資料表的限制式 `battle_appeal`
--
ALTER TABLE `battle_appeal`
  ADD CONSTRAINT `FK_BATTLE_APPEAL_ADMIN` FOREIGN KEY (`ADMIN_ID`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_APPEAL_BATTLE` FOREIGN KEY (`BATTLE_ID`) REFERENCES `battle_record` (`BATTLE_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_APPEAL_COMPLAINANT` FOREIGN KEY (`COMPLAINANT_MEM_ID`) REFERENCES `member` (`MEM_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_APPEAL_RESPONDENT` FOREIGN KEY (`RESPONDENT_MEM_ID`) REFERENCES `member` (`MEM_ID`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `battle_manage_record`
--
ALTER TABLE `battle_manage_record`
  ADD CONSTRAINT `FK_BATTLE_MANAGE_ADMIN` FOREIGN KEY (`ADMIN_ID`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_MANAGE_BATTLE` FOREIGN KEY (`BATTLE_ID`) REFERENCES `battle_record` (`BATTLE_ID`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `battle_record`
--
ALTER TABLE `battle_record`
  ADD CONSTRAINT `FK_BATTLE_CITY` FOREIGN KEY (`CITY_ID`) REFERENCES `city` (`CITY_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_DISTRICT` FOREIGN KEY (`DISTRICT_ID`) REFERENCES `district` (`DISTRICT_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_INITIATOR` FOREIGN KEY (`INITIATOR_ID`) REFERENCES `member` (`MEM_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BATTLE_PARTICIPANT` FOREIGN KEY (`PARTICIPANT_ID`) REFERENCES `member` (`MEM_ID`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `district`
--
ALTER TABLE `district`
  ADD CONSTRAINT `FK_DISTRICT_CITY` FOREIGN KEY (`CITY_ID`) REFERENCES `city` (`CITY_ID`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `exchange_comment`
--
ALTER TABLE `exchange_comment`
  ADD CONSTRAINT `fk_exchange_comment_member` FOREIGN KEY (`mem_id`) REFERENCES `member` (`MEM_ID`),
  ADD CONSTRAINT `fk_exchange_comment_post` FOREIGN KEY (`post_id`) REFERENCES `exchange_post` (`post_id`);

--
-- 資料表的限制式 `exchange_post`
--
ALTER TABLE `exchange_post`
  ADD CONSTRAINT `exchange_post_ibfk_1` FOREIGN KEY (`CITY_ID`) REFERENCES `city` (`CITY_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exchange_post_ibfk_2` FOREIGN KEY (`DISTRICT_ID`) REFERENCES `district` (`DISTRICT_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exchange_post_ibfk_3` FOREIGN KEY (`comm_id`) REFERENCES `exchange_comment` (`comm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exchange_post_ibfk_4` FOREIGN KEY (`mem_id`) REFERENCES `member` (`MEM_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_article` FOREIGN KEY (`art_id`) REFERENCES `article` (`art_id`),
  ADD CONSTRAINT `fk_message_member` FOREIGN KEY (`mem_id`) REFERENCES `member` (`MEM_ID`);

--
-- 資料表的限制式 `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_member` FOREIGN KEY (`mem_id`) REFERENCES `member` (`MEM_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
