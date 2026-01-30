/**
 * Authentication Hook
 *
 * Provides authentication state and helper functions
 * Uses Zustand for state management
 */

import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { type User, type AuthState } from '@types';

// Auth store interface
interface AuthStore extends AuthState {
  setUser: (user: User | null) => void;
  setAuthKey: (authKey: string | null) => void;
  login: (user: User, authKey: string) => void;
  logout: () => void;
  hasPermission: (permission: string) => boolean;
  hasMinClass: (minClass: number) => boolean;
}

// Create auth store with persistence
const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      isAuthenticated: false,
      user: null,
      authKey: null,

      setUser: (user) =>
        set({
          user,
          isAuthenticated: user !== null,
        }),

      setAuthKey: (authKey) => set({ authKey }),

      login: (user, authKey) =>
        set({
          user,
          authKey,
          isAuthenticated: true,
        }),

      logout: () =>
        set({
          user: null,
          authKey: null,
          isAuthenticated: false,
        }),

      hasPermission: (permission) => {
        const { user } = get();
        if (!user) return false;
        return user.permissions?.[permission] === true;
      },

      hasMinClass: (minClass) => {
        const { user } = get();
        if (!user) return false;
        return user.class?.level >= minClass;
      },
    }),
    {
      name: 'gazelle-auth',
      partialize: (state) => ({
        authKey: state.authKey,
        // Don't persist full user data for security
      }),
    }
  )
);

/**
 * useAuth Hook
 *
 * Returns authentication state and helper functions
 */
export function useAuth() {
  const {
    isAuthenticated,
    user,
    authKey,
    setUser,
    setAuthKey,
    login,
    logout,
    hasPermission,
    hasMinClass,
  } = useAuthStore();

  return {
    isAuthenticated,
    user,
    authKey,
    setUser,
    setAuthKey,
    login,
    logout,
    hasPermission,
    hasMinClass,
  };
}

export default useAuth;
