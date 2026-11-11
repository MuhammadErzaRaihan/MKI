export let activeLanguage = 'id';

export function setLanguage(langCode) {
  activeLanguage = langCode;
  
  const radio = document.getElementById(`radio-lang-${langCode}`);
  if (radio) radio.checked = true;

  if (window.openModal) {
    const langName = langCode === 'id' ? 'Bahasa Indonesia' : 'English (US)';
    window.openModal(`Bahasa pengantar sistem diubah ke <strong>${langName}</strong>.`);
  }
}