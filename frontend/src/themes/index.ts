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
export type GazelleTheme = 'oppai' | 'beluga' | 'genaviv' | 'kuro' | 'clifford';

// Map Gazelle themes to Splunk theme configurations
export const THEME_CONFIG: Record<GazelleTheme, {
  family: ThemeFamily;
  colorScheme: ColorScheme;
  density: ThemeDensity;
}> = {
  oppai: {
    family: 'prisma',
    colorScheme: 'dark',
    density: 'comfortable',
  },
  beluga: {
    family: 'prisma',
    colorScheme: 'dark',
    density: 'compact',
  },
  genaviv: {
    family: 'enterprise',
    colorScheme: 'dark',
    density: 'compact',
  },
  kuro: {
    family: 'prisma',
    colorScheme: 'dark',
    density: 'comfortable',
  },
  clifford: {
    family: 'enterprise',
    colorScheme: 'light',
    density: 'comfortable',
  },
};

// Default theme configuration - dark by default to match Gazelle's community identity
export const DEFAULT_THEME: GazelleTheme = 'oppai';
export const DEFAULT_FAMILY: ThemeFamily = 'prisma';
export const DEFAULT_COLOR_SCHEME: ColorScheme = 'dark';
export const DEFAULT_DENSITY: ThemeDensity = 'comfortable';

// Clifford the Big Red Dog themed palette
// Inspired by the warm, friendly aesthetic of Birdwell Island
export const cliffordPalette = {
  // The Big Red Dog himself - primary reds
  cliffordRed: '#D42A2A',           // Clifford's iconic red fur
  cliffordRedDark: '#B71C1C',       // Darker shade for depth
  cliffordRedLight: '#EF5350',      // Lighter red for highlights
  cliffordNose: '#1A1A1A',          // His big black nose

  // Birdwell Island - warm sunny backgrounds
  bgPage: '#FFF8E7',                // Warm cream (sunny day)
  bgHeader: '#D42A2A',              // Clifford red header!
  bgCard: '#FFFFFF',                // Clean white cards
  bgTableHeader: '#FFECB3',         // Warm yellow
  bgTableRow: '#FFFAF0',            // Floral white
  bgTableRowAlt: '#FFF3E0',         // Papaya whip alternating
  bgInput: '#FFFFFF',               // White inputs
  bgHover: '#FFEBEE',               // Light red hover

  // Sky and nature
  skyBlue: '#87CEEB',               // Bright blue sky
  skyBlueDark: '#5DADE2',           // Deeper sky
  grassGreen: '#7CB342',            // Birdwell Island grass
  grassGreenDark: '#558B2F',        // Darker grass/trees
  sunflowerYellow: '#FFD54F',       // Sunny warmth
  sandyBeach: '#F5DEB3',            // Beach/sand color

  // Text colors - warm browns
  textPrimary: '#3E2723',           // Dark brown (like a dog's eyes)
  textSecondary: '#5D4037',         // Medium brown
  textMuted: '#8D6E63',             // Light brown
  textDisabled: '#BCAAA4',          // Faded brown
  textOnRed: '#FFFFFF',             // White text on red backgrounds

  // Emily Elizabeth's dress blue
  emilyBlue: '#5C6BC0',             // Her signature color
  emilyBlueDark: '#3F51B5',

  // Link colors
  link: '#D42A2A',                  // Clifford red links!
  linkHover: '#B71C1C',             // Darker on hover
  linkVisited: '#7B1FA2',           // Purple for visited

  // Status colors
  seeders: '#7CB342',               // Grass green for seeders
  leechers: '#D42A2A',              // Clifford red for leechers
  freeleech: '#FFD54F',             // Sunflower yellow
  snatched: '#5C6BC0',              // Emily blue

  // Borders
  border: '#FFCCBC',                // Warm peach border
  borderLight: '#FFE0B2',           // Lighter border
  borderRed: '#EF9A9A',             // Red-tinted border

  // Badges
  badgeBg: '#FFEBEE',
  badgeBorder: '#D42A2A',
  badgeText: '#B71C1C',

  // T-Bone (yellow lab friend)
  tBoneYellow: '#FDD835',
  // Cleo (purple poodle friend)
  cleoPurple: '#9C27B0',
  // Mac (blue dog friend)
  macBlue: '#2196F3',
};

// Custom Gazelle dark color palette - matches the community look and feel
// These colors are specifically chosen to maintain Gazelle's signature dark theme
export const gazelleDarkPalette = {
  // Backgrounds - the signature Gazelle dark blues
  bgPage: '#1a1a2e',        // Main page background
  bgHeader: '#0f0f1a',      // Header/navigation
  bgCard: '#16213e',        // Cards and panels
  bgTableHeader: '#2c3e50', // Table headers
  bgTableRow: '#1e2a38',    // Table rows
  bgTableRowAlt: '#162029', // Alternating table rows
  bgInput: '#21262d',       // Form inputs
  bgHover: '#21262d',       // Hover state

  // Text colors
  textPrimary: '#f0f6fc',   // Headers, important text
  textSecondary: '#c9d1d9', // Normal body text
  textMuted: '#8b949e',     // Secondary/muted text
  textDisabled: '#6e7681',  // Disabled text

  // Link colors
  link: '#58a6ff',          // Primary links
  linkHover: '#79b8ff',     // Link hover state
  linkVisited: '#a371f7',   // Visited links

  // Status colors (matching Gazelle conventions)
  seeders: '#3fb950',       // Green for seeders
  leechers: '#f85149',      // Red for leechers
  freeleech: '#f0c14b',     // Gold for freeleech
  snatched: '#a371f7',      // Purple for snatched

  // Borders
  border: '#30363d',
  borderLight: '#21262d',

  // Badges and tags
  badgeBg: '#388bfd26',
  badgeBorder: '#388bfd',
  badgeText: '#58a6ff',
  sceneBadgeBg: '#f8514926',
  sceneBadgeText: '#f85149',
};

// Gazelle-specific color palette using Splunk theme variables with Gazelle overrides
export const gazelleColors = {
  // Primary brand colors - Gazelle blue
  primary: '#58a6ff',
  primaryHover: '#79b8ff',
  primaryActive: '#388bfd',

  // Status colors - Gazelle community conventions
  success: gazelleDarkPalette.seeders,
  warning: gazelleDarkPalette.freeleech,
  error: gazelleDarkPalette.leechers,
  info: gazelleDarkPalette.link,

  // Text colors
  textPrimary: gazelleDarkPalette.textPrimary,
  textSecondary: gazelleDarkPalette.textSecondary,
  textDisabled: gazelleDarkPalette.textDisabled,

  // Background colors
  backgroundPage: gazelleDarkPalette.bgPage,
  backgroundCard: gazelleDarkPalette.bgCard,
  backgroundHover: gazelleDarkPalette.bgHover,
  backgroundHeader: gazelleDarkPalette.bgHeader,
  backgroundTable: gazelleDarkPalette.bgTableRow,
  backgroundTableAlt: gazelleDarkPalette.bgTableRowAlt,
  backgroundTableHeader: gazelleDarkPalette.bgTableHeader,
  backgroundInput: gazelleDarkPalette.bgInput,

  // Border colors
  border: gazelleDarkPalette.border,
  borderLight: gazelleDarkPalette.borderLight,

  // Link colors
  link: gazelleDarkPalette.link,
  linkHover: gazelleDarkPalette.linkHover,
  linkVisited: gazelleDarkPalette.linkVisited,

  // Special Gazelle colors
  freeleech: gazelleDarkPalette.freeleech,
  neutralLeech: '#2196F3',
  warned: '#FF9800',
  disabled: '#F44336',
  donor: '#9C27B0',
  seeders: gazelleDarkPalette.seeders,
  leechers: gazelleDarkPalette.leechers,
  snatched: gazelleDarkPalette.snatched,

  // Badge colors
  badge: {
    background: gazelleDarkPalette.badgeBg,
    border: gazelleDarkPalette.badgeBorder,
    text: gazelleDarkPalette.badgeText,
  },
  sceneBadge: {
    background: gazelleDarkPalette.sceneBadgeBg,
    text: gazelleDarkPalette.sceneBadgeText,
  },
};

// Clifford theme colors - warm, friendly, and BIG RED
export const cliffordColors = {
  // Primary brand colors - Clifford's fur!
  primary: cliffordPalette.cliffordRed,
  primaryHover: cliffordPalette.cliffordRedDark,
  primaryActive: cliffordPalette.cliffordRedLight,

  // Status colors - Birdwell Island inspired
  success: cliffordPalette.grassGreen,
  warning: cliffordPalette.sunflowerYellow,
  error: cliffordPalette.cliffordRed,
  info: cliffordPalette.skyBlue,

  // Text colors - warm browns
  textPrimary: cliffordPalette.textPrimary,
  textSecondary: cliffordPalette.textSecondary,
  textDisabled: cliffordPalette.textDisabled,
  textOnPrimary: cliffordPalette.textOnRed,

  // Background colors - sunny Birdwell Island
  backgroundPage: cliffordPalette.bgPage,
  backgroundCard: cliffordPalette.bgCard,
  backgroundHover: cliffordPalette.bgHover,
  backgroundHeader: cliffordPalette.bgHeader,
  backgroundTable: cliffordPalette.bgTableRow,
  backgroundTableAlt: cliffordPalette.bgTableRowAlt,
  backgroundTableHeader: cliffordPalette.bgTableHeader,
  backgroundInput: cliffordPalette.bgInput,

  // Border colors - warm peach tones
  border: cliffordPalette.border,
  borderLight: cliffordPalette.borderLight,

  // Link colors - big red links!
  link: cliffordPalette.link,
  linkHover: cliffordPalette.linkHover,
  linkVisited: cliffordPalette.linkVisited,

  // Special colors
  freeleech: cliffordPalette.sunflowerYellow,
  neutralLeech: cliffordPalette.skyBlue,
  warned: cliffordPalette.sunflowerYellow,
  disabled: cliffordPalette.textDisabled,
  donor: cliffordPalette.cleoPurple,
  seeders: cliffordPalette.seeders,
  leechers: cliffordPalette.leechers,
  snatched: cliffordPalette.snatched,

  // Character colors for fun accents
  clifford: cliffordPalette.cliffordRed,
  emily: cliffordPalette.emilyBlue,
  tBone: cliffordPalette.tBoneYellow,
  cleo: cliffordPalette.cleoPurple,
  mac: cliffordPalette.macBlue,

  // Badge colors
  badge: {
    background: cliffordPalette.badgeBg,
    border: cliffordPalette.badgeBorder,
    text: cliffordPalette.badgeText,
  },
  sceneBadge: {
    background: '#FFF3E0',
    text: cliffordPalette.cliffordRedDark,
  },
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
  cliffordColors,
  gazelleDarkPalette,
  cliffordPalette,
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
