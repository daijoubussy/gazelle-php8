/**
 * Card Component
 *
 * Wrapper around Splunk UI styling for card layouts
 * Corresponds to Gazelle's .box.pad styling
 */

import React from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';

import { IconChevronDown, IconChevronUp } from '@components/Icons';

// Card container
const CardContainer = styled.div<{ $collapsible?: boolean }>`
  background-color: ${variables.backgroundColorSection};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  overflow: hidden;
`;

// Card header
const CardHeader = styled.div<{ $collapsible?: boolean; $collapsed?: boolean }>`
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: ${variables.spacing} ${variables.spacingLarge};
  background-color: ${variables.backgroundColorPage};
  border-bottom: ${({ $collapsed }) => ($collapsed ? 'none' : `1px solid ${variables.borderColor}`)};
  cursor: ${({ $collapsible }) => ($collapsible ? 'pointer' : 'default')};

  &:hover {
    background-color: ${({ $collapsible }) =>
      $collapsible ? variables.backgroundColorHover : variables.backgroundColorPage};
  }
`;

// Card title
const CardTitle = styled.h3`
  margin: 0;
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

// Card subtitle
const CardSubtitle = styled.p`
  margin: ${variables.spacingQuarter} 0 0;
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

// Card header content wrapper
const CardHeaderContent = styled.div`
  flex: 1;
`;

// Card header actions
const CardHeaderActions = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
`;

// Card body
const CardBody = styled.div<{ $padding?: 'none' | 'small' | 'medium' | 'large' }>`
  padding: ${({ $padding }) => {
    switch ($padding) {
      case 'none':
        return '0';
      case 'small':
        return variables.spacingHalf;
      case 'large':
        return variables.spacingXLarge;
      default:
        return variables.spacingLarge;
    }
  }};
`;

// Card footer
const CardFooter = styled.div`
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: ${variables.spacing};
  padding: ${variables.spacing} ${variables.spacingLarge};
  background-color: ${variables.backgroundColorPage};
  border-top: 1px solid ${variables.borderColor};
`;

// Collapse icon
const CollapseIcon = styled.span`
  display: flex;
  align-items: center;
  color: ${variables.textGray};
`;

// Props
export interface CardProps {
  title?: string;
  subtitle?: string;
  children: React.ReactNode;
  footer?: React.ReactNode;
  actions?: React.ReactNode;
  collapsible?: boolean;
  defaultCollapsed?: boolean;
  padding?: 'none' | 'small' | 'medium' | 'large';
  className?: string;
}

/**
 * Card Component
 */
const Card: React.FC<CardProps> = ({
  title,
  subtitle,
  children,
  footer,
  actions,
  collapsible = false,
  defaultCollapsed = false,
  padding = 'medium',
  className,
}) => {
  const [collapsed, setCollapsed] = React.useState(defaultCollapsed);

  const handleToggleCollapse = () => {
    if (collapsible) {
      setCollapsed((prev) => !prev);
    }
  };

  return (
    <CardContainer className={className} $collapsible={collapsible}>
      {(title || actions) && (
        <CardHeader
          $collapsible={collapsible}
          $collapsed={collapsed}
          onClick={handleToggleCollapse}
        >
          <CardHeaderContent>
            {title && <CardTitle>{title}</CardTitle>}
            {subtitle && <CardSubtitle>{subtitle}</CardSubtitle>}
          </CardHeaderContent>
          <CardHeaderActions>
            {actions}
            {collapsible && (
              <CollapseIcon>
                {collapsed ? (
                  <IconChevronDown size="medium" />
                ) : (
                  <IconChevronUp size="medium" />
                )}
              </CollapseIcon>
            )}
          </CardHeaderActions>
        </CardHeader>
      )}

      {!collapsed && <CardBody $padding={padding}>{children}</CardBody>}

      {footer && !collapsed && <CardFooter>{footer}</CardFooter>}
    </CardContainer>
  );
};

// Sub-components for composition
Card.Header = CardHeader;
Card.Title = CardTitle;
Card.Subtitle = CardSubtitle;
Card.Body = CardBody;
Card.Footer = CardFooter;

export default Card;
