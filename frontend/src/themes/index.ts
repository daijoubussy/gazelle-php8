/**
 * Gazelle Theme Configuration
 *
 * Uses Splunk UI Toolkit themes with Gazelle-specific customizations
 * Defaults to Splunk's enterprise theme family
 */

import { variables, pick } from '@splunk/themes';
import { css } from 'styled-components';

// Theme family options: 'enterprise' | 'prisma'
export type ThemeFamily = 'enterprise' | 'prisma';

// Color scheme options
export type ColorScheme = 'light' | 'dark';

// Density options: 'comfortable' | 'compact'
export type ThemeDensity = 'comfortable' | 'compact';

// Gazelle theme names mapped to Splunk themes
export type GazelleTheme = 'oppai' | 'beluga' | 'genaviv' | 'kuro';

// Map Gazelle themes to Splunk theme configurations
export const THEME_CONFIG: Record<GazelleTheme, {
  family: ThemeFamily;
  colorScheme: ColorScheme;
  density: ThemeDensity;
}> = {
  oppai: {
    family: 'enterprise',
    colorScheme: 'light',
    density: 'comfortable',
  },
  beluga: {
    family: 'prisma',
    colorScheme: 'light',
    density: 'compact',
  },
  genaviv: {
    family: 'enterprise',
    colorScheme: 'light',
    density: 'compact',
  },
  kuro: {
    family: 'prisma',
    colorScheme: 'dark',
    density: 'comfortable',
  },
};

// Default theme configuration
export const DEFAULT_THEME: GazelleTheme = 'oppai';
export const DEFAULT_FAMILY: ThemeFamily = 'enterprise';
export const DEFAULT_COLOR_SCHEME: ColorScheme = 'light';
export const DEFAULT_DENSITY: ThemeDensity = 'comfortable';

// Gazelle-specific color palette using Splunk theme variables
export const gazelleColors = {
  // Primary brand colors using Splunk's pick() for theme compatibility
  primary: pick({
    enterprise: variables.brandColor,
    prisma: variables.brandColorL10,
  }),
  primaryHover: pick({
    enterprise: variables.brandColorL10,
    prisma: variables.brandColorL20,
  }),
  primaryActive: pick({
    enterprise: variables.brandColorD10,
    prisma: variables.brandColor,
  }),

  // Status colors
  success: pick({
    enterprise: variables.successColor,
    prisma: variables.successColorL10,
  }),
  warning: pick({
    enterprise: variables.warningColor,
    prisma: variables.warningColorL10,
  }),
  error: pick({
    enterprise: variables.errorColor,
    prisma: variables.errorColorL10,
  }),
  info: pick({
    enterprise: variables.infoColor,
    prisma: variables.infoColorL10,
  }),

  // Text colors
  textPrimary: pick({
    enterprise: variables.textColor,
    prisma: variables.contentColorDefault,
  }),
  textSecondary: pick({
    enterprise: variables.textGray,
    prisma: variables.contentColorMuted,
  }),
  textDisabled: pick({
    enterprise: variables.textDisabledColor,
    prisma: variables.contentColorDisabled,
  }),

  // Background colors
  backgroundPage: pick({
    enterprise: variables.backgroundColor,
    prisma: variables.backgroundColorPage,
  }),
  backgroundCard: pick({
    enterprise: variables.white,
    prisma: variables.backgroundColorSection,
  }),
  backgroundHover: pick({
    enterprise: variables.backgroundColorHover,
    prisma: variables.interactiveColorHover,
  }),

  // Border colors
  border: pick({
    enterprise: variables.borderColor,
    prisma: variables.borderColor,
  }),
  borderLight: pick({
    enterprise: variables.borderLightColor,
    prisma: variables.borderColorLight,
  }),

  // Special Gazelle colors
  freeleech: '#4CAF50',
  neutralLeech: '#2196F3',
  warned: '#FF9800',
  disabled: '#F44336',
  donor: '#9C27B0',
};

// Spacing constants using Splunk defaults
export const spacing = {
  xxs: variables.spacingQuarter,
  xs: variables.spacingHalf,
  sm: variables.spacing,
  md: variables.spacingLarge,
  lg: variables.spacingXLarge,
  xl: variables.spacingXXLarge,
};

// Typography using Splunk defaults
export const typography = {
  fontFamily: variables.fontFamily,
  fontFamilyMono: variables.fontFamilyMonospace,
  fontSize: {
    xs: variables.fontSizeSmall,
    sm: variables.fontSize,
    md: variables.fontSizeLarge,
    lg: variables.fontSizeXLarge,
    xl: variables.fontSizeXXLarge,
  },
  fontWeight: {
    normal: variables.fontWeightNormal,
    medium: variables.fontWeightSemiBold,
    bold: variables.fontWeightBold,
  },
  lineHeight: {
    tight: variables.lineHeight,
    normal: variables.lineHeightTall,
  },
};

// Border radius using Splunk defaults
export const borderRadius = {
  sm: variables.borderRadiusSmall,
  md: variables.borderRadius,
  lg: variables.borderRadiusLarge,
  round: '50%',
};

// Shadow definitions
export const shadows = {
  sm: variables.embossShadow,
  md: variables.overlayShadow,
  lg: variables.modalShadow,
};

// Z-index scale
export const zIndex = {
  dropdown: variables.zindexLayer,
  sticky: variables.zindexFixed,
  modal: variables.zindexModal,
  tooltip: variables.zindexTooltip,
};

// Breakpoints for responsive design
export const breakpoints = {
  xs: '480px',
  sm: '640px',
  md: '768px',
  lg: '1024px',
  xl: '1280px',
  xxl: '1536px',
};

// Media query helpers
export const media = {
  xs: `@media (min-width: ${breakpoints.xs})`,
  sm: `@media (min-width: ${breakpoints.sm})`,
  md: `@media (min-width: ${breakpoints.md})`,
  lg: `@media (min-width: ${breakpoints.lg})`,
  xl: `@media (min-width: ${breakpoints.xl})`,
  xxl: `@media (min-width: ${breakpoints.xxl})`,
};

// Common styled-components mixins
export const mixins = {
  // Reset box model
  reset: css`
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  `,

  // Flex container
  flexCenter: css`
    display: flex;
    align-items: center;
    justify-content: center;
  `,

  flexBetween: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,

  // Text truncation
  truncate: css`
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  `,

  // Visually hidden but accessible
  visuallyHidden: css`
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  `,

  // Focus ring
  focusRing: css`
    outline: none;
    box-shadow: 0 0 0 2px ${variables.focusColor};
  `,

  // Card style
  card: css`
    background: ${gazelleColors.backgroundCard};
    border: 1px solid ${gazelleColors.border};
    border-radius: ${borderRadius.md};
    box-shadow: ${shadows.sm};
  `,

  // Link style (Gazelle's bracket style)
  bracketLink: css`
    &::before {
      content: '[';
      color: ${gazelleColors.textSecondary};
    }
    &::after {
      content: ']';
      color: ${gazelleColors.textSecondary};
    }
  `,
};

// Transition presets
export const transitions = {
  fast: 'all 0.15s ease-in-out',
  normal: 'all 0.25s ease-in-out',
  slow: 'all 0.35s ease-in-out',
};

export default {
  THEME_CONFIG,
  DEFAULT_THEME,
  DEFAULT_FAMILY,
  DEFAULT_COLOR_SCHEME,
  DEFAULT_DENSITY,
  gazelleColors,
  spacing,
  typography,
  borderRadius,
  shadows,
  zIndex,
  breakpoints,
  media,
  mixins,
  transitions,
};
