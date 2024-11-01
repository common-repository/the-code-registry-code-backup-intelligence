const defaultTheme = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
  important: '#code-intelligence-app',
  content: [
    "./admin/js/src/**/*.{vue,js,ts,jsx,tsx}"
  ],
  theme: {
    extend: {
      colors: {
        'brand-blue': '#0F2556',
        'brand-purple': '#5338F9'
      },
      fontFamily: {
        sans: ['Poppins', ...defaultTheme.fontFamily.sans],
        serif: ['DM Serif Display', ...defaultTheme.fontFamily.serif]
      },
      screens: {
        '3xl': '1700px'
      }
    }
  },
  plugins: [],
}