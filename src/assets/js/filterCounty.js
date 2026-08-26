//重要!!! 判斷當前環境
const phpBaseUrl =
   location.hostname === "localhost" ||
      location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : `${location.origin}/ckd101/g2/php`;



// == 縣市篩選 ======================================
const selectCity = document.getElementById('select-city');
const selectDistrict = document.getElementById('select-district');


//向後端資料庫取得縣市資料
function fetchCities() {
   fetch(`${phpBaseUrl}/location/cities_get.php`).then(res => res.json()).then(data => {
      data.forEach(city => {
         const option = document.createElement('option');

         option.value = city.CITY_ID;
         option.textContent = city.CITY_NAME;
         selectCity.append(option);
      });
   });
}

fetchCities();

//根據使用者所選縣市，再向後端取得對應的行政區資料
function fetchDistricts(cityId) {
   fetch(`${phpBaseUrl}/location/districts_get.php?city_id=${cityId}`).then(res => res.json()).then(data => {
      data.forEach(district => {
         const option = document.createElement('option');

         option.value = district.DISTRICT_ID;
         option.textContent = district.DISTRICT_NAME;

         selectDistrict.append(option);
      });
   });
}

//監聽縣市select，當縣市選項切換時，重新render行政區選項
selectCity.addEventListener('change', function () {
   //先清除舊的行政區選項
   selectDistrict.innerHTML = `<option value="">選擇行政區</option>`;

   //使用者如果沒選擇縣市時，行政區欄位要維持disabled
   if (!this.value) {
      selectDistrict.disabled = true;
      return;
   }

   //有選擇縣市時，行政區欄位恢復可選狀態，且呼叫行政區api
   selectDistrict.disabled = false;
   fetchDistricts(this.value);
})
