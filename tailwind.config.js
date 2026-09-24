/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#F5F8FC',
          100: '#EAF3FB',
          200: '#D9E3EF',
          300: '#A0C9EF',
          400: '#4B9FE5',
          500: '#1685D8', // Bright Blue
          600: '#1268B3', // Primary Blue
          700: '#0E5596',
          800: '#0B3A70', // Navy
          900: '#062A52', // Deep Navy
          950: '#041B35',
        },
        navy: {
          700: '#102A43',
          800: '#0B3A70',
          900: '#062A52',
          950: '#03172E',
        },
        accent: {
          50: '#FFF9ED',
          100: '#FFF2D7',
          200: '#FFE1A6',
          300: '#FFCE74',
          400: '#FFB52E', // Bright Orange
          500: '#F5A623', // Accent Orange
          600: '#DF8E12',
          700: '#B56F06',
          800: '#8C5204',
          900: '#643702',
        },
        gold: {
          400: '#FFB52E',
          500: '#F5A623',
          600: '#DF8E12',
        },
        can: {
          deepnavy: '#062A52',
          navy: '#0B3A70',
          primary: '#1268B3',
          brightblue: '#1685D8',
          accent: '#F5A623',
          brightorange: '#FFB52E',
          white: '#FFFFFF',
          lightbg: '#F5F8FC',
          softblue: '#EAF3FB',
          text: '#102A43',
          subtext: '#52667A',
          border: '#D9E3EF',
        }
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
