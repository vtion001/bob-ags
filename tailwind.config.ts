import type { Config } from 'tailwindcss'

const config: Config = {
  content: [
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        navy: {
          950: '#050D18',
          900: '#0A1628',
          800: '#0F1F33',
          700: '#1A2E47',
          600: '#2A4A6B',
        },
        cyan: {
          500: '#00D4FF',
          400: '#00E0FF',
          300: '#33DDFF',
        },
        slate: {
          500: '#64748B',
          400: '#94A3B8',
        },
      },
      borderRadius: {
        DEFAULT: '8px',
        sm: '6px',
        xs: '4px',
      },
      spacing: {
        gutter: '24px',
      },
      boxShadow: {
        'glow-cyan': '0 0 20px rgba(0, 212, 255, 0.15)',
        'glow-cyan-md': '0 0 30px rgba(0, 212, 255, 0.25)',
      },
      animation: {
        'pulse-glow': 'pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      },
      keyframes: {
        'pulse-glow': {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.5' },
        },
      },
    },
  },
  plugins: [],
}

export default config
