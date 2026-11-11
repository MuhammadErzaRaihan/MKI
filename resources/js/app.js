import './bootstrap';
import { setTheme } from './modules/theme.js';
import { updateLiveClockAndWeather } from './modules/clock.js';
import { switchTab, toggleMobileSidebar, openModal, closeModal } from './modules/navigation.js';
import { initCharts, initFullTrafficChart, updateChartsColor } from './modules/charts.js';
import { renderCredentials, searchCredentials, openCredentialModal, closeCredentialModal, generateSecretKey, editCredential, deleteCredential } from './modules/credentials.js';

window.setTheme = setTheme;
window.switchTab = switchTab;
window.toggleMobileSidebar = toggleMobileSidebar;
window.openModal = openModal;
window.closeModal = closeModal;
window.initFullTrafficChart = initFullTrafficChart;
window.updateChartsColor = updateChartsColor;

window.renderCredentials = renderCredentials;
window.searchCredentials = searchCredentials;
window.openCredentialModal = openCredentialModal;
window.closeCredentialModal = closeCredentialModal;
window.generateSecretKey = generateSecretKey;
window.editCredential = editCredential;
window.deleteCredential = deleteCredential;

window.addEventListener('DOMContentLoaded', () => {
  initCharts();
  setTheme('clean');
  renderCredentials();
  updateLiveClockAndWeather();
  setInterval(updateLiveClockAndWeather, 1000);
});