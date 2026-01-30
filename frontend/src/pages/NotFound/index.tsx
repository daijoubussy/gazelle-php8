/**
 * Not Found (404) Page
 *
 * Displayed when a route is not found
 */

import React from 'react';
import { useNavigate } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import SplunkThemeProvider from '@splunk/themes/SplunkThemeProvider';

import { DEFAULT_FAMILY, DEFAULT_COLOR_SCHEME, DEFAULT_DENSITY } from '@themes';
import { IconWarning, IconHome } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: ${variables.spacingLarge};
  background-color: ${variables.backgroundColorPage};
`;

const Content = styled.div`
  text-align: center;
  max-width: 500px;
`;

const IconWrapper = styled.div`
  display: flex;
  justify-content: center;
  margin-bottom: ${variables.spacingLarge};
  color: ${variables.warningColor};
`;

const ErrorCode = styled.h1`
  margin: 0;
  font-size: 6rem;
  font-weight: ${variables.fontWeightBold};
  color: ${variables.textColor};
  line-height: 1;
`;

const ErrorTitle = styled.h2`
  margin: ${variables.spacing} 0;
  font-size: ${variables.fontSizeXXLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const ErrorDescription = styled.p`
  margin: 0 0 ${variables.spacingLarge};
  font-size: ${variables.fontSize};
  color: ${variables.textGray};
`;

const ButtonGroup = styled.div`
  display: flex;
  justify-content: center;
  gap: ${variables.spacing};
`;

/**
 * Not Found Page Component
 */
const NotFoundPage: React.FC = () => {
  const navigate = useNavigate();

  return (
    <SplunkThemeProvider
      family={DEFAULT_FAMILY}
      colorScheme={DEFAULT_COLOR_SCHEME}
      density={DEFAULT_DENSITY}
    >
      <PageContainer>
        <Content>
          <IconWrapper>
            <IconWarning size="xlarge" />
          </IconWrapper>
          <ErrorCode>404</ErrorCode>
          <ErrorTitle>Page Not Found</ErrorTitle>
          <ErrorDescription>
            The page you are looking for does not exist or has been moved.
            Please check the URL or navigate back to the home page.
          </ErrorDescription>
          <ButtonGroup>
            <Button
              appearance="primary"
              icon={<IconHome />}
              onClick={() => navigate('/')}
              label="Go Home"
            />
            <Button
              appearance="secondary"
              onClick={() => navigate(-1)}
              label="Go Back"
            />
          </ButtonGroup>
        </Content>
      </PageContainer>
    </SplunkThemeProvider>
  );
};

export default NotFoundPage;
