const phpBaseUrl =
   location.hostname === "localhost" ||
      location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : `${location.origin}/ckd101/g2/php`;

export function fetchCities(selectCityEl) {
   fetch(`${phpBaseUrl}/location/cities_get.php`)
      .then(res => res.json())
      .then(data => {
         data.forEach(city => {
            const option = document.createElement('option');
            option.value = city.CITY_ID;
            option.textContent = city.CITY_NAME;
            selectCityEl.append(option);
         });
      });
}

export function fetchDistricts(cityId, selectDistrictEl) {
   return fetch(`${phpBaseUrl}/location/districts_get.php?CITY_ID=${cityId}`)
      .then(res => res.json())
      .then(data => {
         data.forEach(district => {
            const option = document.createElement('option');
            option.value = district.DISTRICT_ID;
            option.textContent = district.DISTRICT_NAME;
            selectDistrictEl.append(option);
         });
      });
}

// 初始化縣市/行政區聯動,並讓外部可以傳入「選擇改變後」要額外做的事
export function initCityDistrictSelector(selectCityEl, selectDistrictEl, onChangeCallback) {
   fetchCities(selectCityEl);

   selectCityEl.addEventListener('change', function () {
      selectDistrictEl.innerHTML = `<option value="">選擇行政區</option>`;

      if (!this.value) {
         selectDistrictEl.disabled = true;
      } else {
         selectDistrictEl.disabled = false;
         fetchDistricts(this.value, selectDistrictEl).then(() => {
            if (onChangeCallback) onChangeCallback();
         });
         return; // 避免行政區還沒 fetch 完就先觸發 callback
      }

      if (onChangeCallback) onChangeCallback();
   });

   selectDistrictEl.addEventListener('change', function () {
      if (onChangeCallback) onChangeCallback();
   });
}