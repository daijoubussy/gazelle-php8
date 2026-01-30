/**
 * Form Components
 *
 * Reusable form building blocks using Splunk UI
 */

import React from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Text from '@splunk/react-ui/Text';
import TextArea from '@splunk/react-ui/TextArea';
import Select from '@splunk/react-ui/Select';
import Switch from '@splunk/react-ui/Switch';
import Button from '@splunk/react-ui/Button';
import Message from '@splunk/react-ui/Message';

import { IconInfo, IconWarning, IconError, IconSuccess } from '@components/Icons';

// Form container
const FormContainer = styled.form`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

// Form section
const FormSectionContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const FormSectionTitle = styled.h3`
  margin: 0;
  padding-bottom: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

// Form group
const FormGroupContainer = styled.div<{ $inline?: boolean }>`
  display: ${({ $inline }) => ($inline ? 'grid' : 'flex')};
  grid-template-columns: ${({ $inline }) => ($inline ? '200px 1fr' : 'none')};
  flex-direction: ${({ $inline }) => ($inline ? 'row' : 'column')};
  gap: ${({ $inline }) => ($inline ? variables.spacing : variables.spacingQuarter)};
  align-items: ${({ $inline }) => ($inline ? 'center' : 'stretch')};

  @media (max-width: 600px) {
    display: flex;
    flex-direction: column;
    align-items: stretch;
  }
`;

const Label = styled.label<{ $required?: boolean }>`
  font-size: ${variables.fontSize};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};

  ${({ $required }) =>
    $required &&
    `
    &::after {
      content: ' *';
      color: ${variables.errorColor};
    }
  `}
`;

const HelpText = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const ErrorText = styled.span`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.errorColor};
`;

// Form actions
const FormActionsContainer = styled.div<{ $align?: 'left' | 'center' | 'right' }>`
  display: flex;
  justify-content: ${({ $align }) =>
    $align === 'left' ? 'flex-start' : $align === 'center' ? 'center' : 'flex-end'};
  gap: ${variables.spacing};
  padding-top: ${variables.spacingLarge};
  border-top: 1px solid ${variables.borderColor};
`;

// Form row for horizontal layouts
const FormRowContainer = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: ${variables.spacing};
`;

// Component interfaces
export interface FormProps extends React.FormHTMLAttributes<HTMLFormElement> {
  children: React.ReactNode;
}

export interface FormSectionProps {
  title?: string;
  children: React.ReactNode;
}

export interface FormGroupProps {
  label?: string;
  htmlFor?: string;
  required?: boolean;
  helpText?: string;
  error?: string;
  inline?: boolean;
  children: React.ReactNode;
}

export interface FormActionsProps {
  align?: 'left' | 'center' | 'right';
  children: React.ReactNode;
}

export interface FormRowProps {
  children: React.ReactNode;
}

/**
 * Form component
 */
export const Form: React.FC<FormProps> = ({ children, ...props }) => {
  return <FormContainer {...props}>{children}</FormContainer>;
};

/**
 * Form section with optional title
 */
export const FormSection: React.FC<FormSectionProps> = ({ title, children }) => {
  return (
    <FormSectionContainer>
      {title && <FormSectionTitle>{title}</FormSectionTitle>}
      {children}
    </FormSectionContainer>
  );
};

/**
 * Form group with label, help text, and error support
 */
export const FormGroup: React.FC<FormGroupProps> = ({
  label,
  htmlFor,
  required,
  helpText,
  error,
  inline,
  children,
}) => {
  return (
    <FormGroupContainer $inline={inline}>
      {label && (
        <Label htmlFor={htmlFor} $required={required}>
          {label}
        </Label>
      )}
      <div>
        {children}
        {helpText && !error && <HelpText>{helpText}</HelpText>}
        {error && (
          <ErrorText>
            <IconError size="small" /> {error}
          </ErrorText>
        )}
      </div>
    </FormGroupContainer>
  );
};

/**
 * Form actions container
 */
export const FormActions: React.FC<FormActionsProps> = ({ align = 'right', children }) => {
  return <FormActionsContainer $align={align}>{children}</FormActionsContainer>;
};

/**
 * Form row for horizontal field layouts
 */
export const FormRow: React.FC<FormRowProps> = ({ children }) => {
  return <FormRowContainer>{children}</FormRowContainer>;
};

// Export Splunk UI form components for convenience
export { Text, TextArea, Select, Switch, Button, Message };

export default Form;
