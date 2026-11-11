/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'arcade-navy': '#0C1456',
        'arcade-lime': '#80F555',
        'arcade-cyan': '#00E5E8',
        'arcade-yellow': '#FDF833',
        'arcade-cream': '#FAF0CA',
        'arcade-bg': '#070C36',
        'arcade-surface': '#15206B',

        'soc-bg': '#090D10',
        'soc-card': '#13181D',
        'soc-surface': '#1B2229',
        'soc-ice': '#CFEFFF',
        'soc-cyan': '#7AD7F0',

        'gov-bg': '#EDF7FC',
        'gov-surface': '#E0F2FE',
        'gov-card': '#FFFFFF',
        'gov-blue': '#0066D6',
        'gov-cyan': '#38BDF8',
        'gov-muted': '#6499CC',
        'gov-dark': '#0F172A',
        'gov-border': '#BAE6FD'
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        brutal: ['Space Grotesk', 'sans-serif'],
        pixel: ['"Press Start 2P"', 'monospace'],
        arcade: ['Silkscreen', 'cursive'],
        orbitron: ['Orbitron', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace']
      }
    }
  },
  plugins: [],
}