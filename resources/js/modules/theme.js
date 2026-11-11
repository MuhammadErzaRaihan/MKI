export let activeTheme = 'clean';

export function setTheme(themeName) {
  activeTheme = themeName;
  document.documentElement.setAttribute('data-theme', themeName);

  const radio = document.getElementById(`radio-theme-${themeName}`);
  if (radio) radio.checked = true;

  if (window.updateChartsColor) {
    window.updateChartsColor();
  }
}