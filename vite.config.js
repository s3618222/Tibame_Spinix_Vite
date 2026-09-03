import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'   // 載入 path

//console.log(__dirname);
//console.log( path.resolve(__dirname, "./src") );


// https://vitejs.dev/config/
export default defineConfig({
  base: "/ckd101/g2/", // 設定相對路徑
  plugins: [vue()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src") // @ 符號：直接指向到 src 資料夾
    }
  },
  build: {
    rolldownOptions: {
      input: {
        index: path.resolve(__dirname, "index.html"),
        homepage: path.resolve(__dirname, "homepage.html"),
        battle: path.resolve(__dirname, "battle.html"),
        createBattle: path.resolve(__dirname, "createBattle.html"),
        build: path.resolve(__dirname, "build.html"),
        forum: path.resolve(__dirname, "forum.html"),
        forumArticle: path.resolve(__dirname, "forumArticle.html"),
        forumForm: path.resolve(__dirname, "forumForm.html"),
        market: path.resolve(__dirname, "market.html"),
        product_detail: path.resolve(__dirname, "product_detail.html"),
        addChange: path.resolve(__dirname, "addChange.html"),
        member: path.resolve(__dirname, "member.html"),
        signIn: path.resolve(__dirname, "signIn.html"),
        signUp: path.resolve(__dirname, "signUp.html"),
        complaint: path.resolve(__dirname, "complaint.html"),
        backMember: path.resolve(__dirname, "backMember.html"),
        backSignIn: path.resolve(__dirname, "backSignIn.html")
      }
    }
  }
})