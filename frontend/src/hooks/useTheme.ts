/**
 * Theme Hook
 *
 * Manages theme state with persistence
 */

import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { type GazelleTheme, DEFAULT_THEME, THEME_CONFIG } from '@themes';

// Theme store interface
interface ThemeStore {
  theme: GazelleTheme;
  setTheme: (theme: GazelleTheme) => void;
}

// Create theme store with persistence
const useThemeStore = create<ThemeStore>()(
  persist(
    (set) => ({
      theme: DEFAULT_THEME,

      setTheme: (theme) => {
        // Validate theme exists in config
        if (THEME_CONFIG[theme]) {
          set({ theme });
        } else {
          console.warn(`Invalid theme: ${theme}, using default`);
          set({ theme: DEFAULT_THEME });
        }
      },
    }),
    {
      name: 'gazelle-theme',
    }
  )
);

/**
 * useTheme Hook
 *
 * Returns theme state and setter
 */
export function useTheme() {
  const { theme, setTheme } = useThemeStore();

  return {
    theme,
    setTheme,
    themeConfig: THEME_CONFIG[theme] || THEME_CONFIG[DEFAULT_THEME],
  };
}

export default useTheme;
