export function updateLiveClockAndWeather() {
  const now = new Date();
  
  let hours = now.getHours();
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;
  const timeStr = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;

  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const dateStr = `${month}/${day}`;

  const days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
  const dayStr = days[now.getDay()];

  const rawHour = now.getHours();
  let timePeriod = 'Morning';
  let weatherIconClass = 'fa-solid fa-sun';
  let weatherIconBg = 'bg-amber-400 text-black';
  let weatherDesc = 'Cerah (Banjarmasin)';

  if (rawHour >= 5 && rawHour < 12) {
    timePeriod = 'Morning';
    weatherIconClass = 'fa-solid fa-sun';
    weatherIconBg = 'bg-amber-400 text-black';
  } else if (rawHour >= 12 && rawHour < 17) {
    timePeriod = 'Afternoon';
    weatherIconClass = 'fa-solid fa-cloud-sun';
    weatherIconBg = 'bg-sky-400 text-slate-950';
    weatherDesc = 'Berawan (Banjarmasin)';
  } else if (rawHour >= 17 && rawHour < 20) {
    timePeriod = 'Evening';
    weatherIconClass = 'fa-solid fa-sun-plant-wilt';
    weatherIconBg = 'bg-orange-500 text-white';
    weatherDesc = 'Senja (Banjarmasin)';
  } else {
    timePeriod = 'Night';
    weatherIconClass = 'fa-solid fa-moon';
    weatherIconBg = 'bg-indigo-600 text-amber-300';
    weatherDesc = 'Cerah Berawan (Banjarmasin)';
  }

  const clockTimeEl = document.getElementById('hud-clock-time');
  const clockDateEl = document.getElementById('hud-clock-date');
  const clockDayEl = document.getElementById('hud-clock-day');
  const periodEl = document.getElementById('hud-time-period');
  const weatherDescEl = document.getElementById('hud-weather-desc');
  const weatherIconEl = document.getElementById('hud-weather-icon');

  if (clockTimeEl) clockTimeEl.innerText = timeStr;
  if (clockDateEl) clockDateEl.innerText = dateStr;
  if (clockDayEl) clockDayEl.innerText = dayStr;
  if (periodEl) periodEl.innerText = timePeriod;
  if (weatherDescEl) weatherDescEl.innerText = weatherDesc;
  if (weatherIconEl) {
    weatherIconEl.className = `w-7 h-7 rounded-full ${weatherIconBg} flex items-center justify-center text-xs font-bold shadow-sm transition-colors`;
    weatherIconEl.innerHTML = `<i class="${weatherIconClass}"></i>`;
  }
}