import './bootstrap';
import { setTheme } from './modules/theme.js';
import { setLanguage } from './modules/language.js';
import { updateLiveClockAndWeather } from './modules/clock.js';
import { switchTab, toggleMobileSidebar, openModal, closeModal } from './modules/navigation.js';
import { initCharts, initFullTrafficChart, updateChartsColor } from './modules/chart.js';
import { 
  renderCredentials, 
  searchCredentials, 
  openCredentialModal, 
  closeCredentialModal, 
  generateSecretKey, 
  previewPhotoUpload,
  saveCredentialForm,
  editCredential, 
  deleteCredential 
} from './modules/credentials.js';

// Global Scope Bindings
window.setTheme = setTheme;
window.setLanguage = setLanguage;
window.switchTab = switchTab;
window.toggleMobileSidebar = toggleMobileSidebar;
window.openModal = openModal;
window.closeModal = closeModal;
window.initFullTrafficChart = initFullTrafficChart;
window.updateChartsColor = updateChartsColor;

// Credential CRUD Scope Bindings
window.renderCredentials = renderCredentials;
window.searchCredentials = searchCredentials;
window.openCredentialModal = openCredentialModal;
window.closeCredentialModal = closeCredentialModal;
window.generateSecretKey = generateSecretKey;
window.previewPhotoUpload = previewPhotoUpload;
window.saveCredentialForm = saveCredentialForm;
window.editCredential = editCredential;
window.deleteCredential = deleteCredential;

window.addEventListener('DOMContentLoaded', () => {
  initCharts();
  setTheme('clean');
  setLanguage('id');
  renderCredentials();
  updateLiveClockAndWeather();
  setInterval(updateLiveClockAndWeather, 1000);
});