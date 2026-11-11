export let activeTheme = 'clean';

export function setTheme(themeName) {
  activeTheme = themeName;
  document.documentElement.setAttribute('data-theme', themeName);

  ['retro', 'soc', 'clean'].forEach(t => {
    const btn = document.getElementById(`btn-theme-${t}`);
    if (btn) {
      if (t === themeName) {
        btn.classList.add('ring-2', 'ring-sky-400', 'scale-[1.02]');
      } else {
        btn.classList.remove('ring-2', 'ring-sky-400', 'scale-[1.02]');
      }
    }
  });

  if (window.updateChartsColor) {
    window.updateChartsColor();
  }
}