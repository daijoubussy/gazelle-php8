/**
 * Login Page
 *
 * Authentication page for user login
 */

import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import Text from '@splunk/react-ui/Text';
import Link from '@splunk/react-ui/Link';
import Message from '@splunk/react-ui/Message';
import SplunkThemeProvider from '@splunk/themes/SplunkThemeProvider';

import { DEFAULT_FAMILY, DEFAULT_COLOR_SCHEME, DEFAULT_DENSITY } from '@themes';
import { IconLock, IconUser, IconInfo } from '@components/Icons';
import { useAuth } from '@hooks/useAuth';

const PageContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: ${variables.spacingLarge};
  background-color: ${variables.backgroundColorPage};
`;

const LoginCard = styled.div`
  width: 100%;
  max-width: 400px;
  padding: ${variables.spacingXLarge};
  background-color: ${variables.backgroundColorSection};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
`;

const Logo = styled.div`
  text-align: center;
  margin-bottom: ${variables.spacingXLarge};
`;

const LogoTitle = styled.h1`
  margin: 0;
  font-size: ${variables.fontSizeXXXLarge};
  font-weight: ${variables.fontWeightBold};
  color: ${variables.brandColor};
`;

const LogoSubtitle = styled.p`
  margin: ${variables.spacingQuarter} 0 0;
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const Form = styled.form`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const FormGroup = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
`;

const Label = styled.label`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  font-size: ${variables.fontSizeSmall};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const FormFooter = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
  margin-top: ${variables.spacing};
`;

const LinksRow = styled.div`
  display: flex;
  justify-content: space-between;
  font-size: ${variables.fontSizeSmall};
`;

const ErrorMessage = styled.div`
  margin-bottom: ${variables.spacing};
`;

/**
 * Login Page Component
 */
const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);

    try {
      await login(username, password);
      navigate('/');
    } catch (err) {
      setError('Invalid username or password');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SplunkThemeProvider
      family={DEFAULT_FAMILY}
      colorScheme={DEFAULT_COLOR_SCHEME}
      density={DEFAULT_DENSITY}
    >
      <PageContainer>
        <LoginCard>
          <Logo>
            <LogoTitle>Gazelle</LogoTitle>
            <LogoSubtitle>Private Tracker</LogoSubtitle>
          </Logo>

          {error && (
            <ErrorMessage>
              <Message type="error">{error}</Message>
            </ErrorMessage>
          )}

          <Form onSubmit={handleSubmit}>
            <FormGroup>
              <Label>
                <IconUser size="small" />
                Username
              </Label>
              <Text
                value={username}
                onChange={(e, { value }) => setUsername(value)}
                placeholder="Enter your username"
                disabled={isLoading}
                autoComplete="username"
              />
            </FormGroup>

            <FormGroup>
              <Label>
                <IconLock size="small" />
                Password
              </Label>
              <Text
                type="password"
                value={password}
                onChange={(e, { value }) => setPassword(value)}
                placeholder="Enter your password"
                disabled={isLoading}
                autoComplete="current-password"
              />
            </FormGroup>

            <FormFooter>
              <Button
                type="submit"
                appearance="primary"
                disabled={isLoading || !username || !password}
                label={isLoading ? 'Signing in...' : 'Sign In'}
              />

              <LinksRow>
                <Link to="/forgot-password">Forgot password?</Link>
                <Link to="/register">Create account</Link>
              </LinksRow>
            </FormFooter>
          </Form>

          <Message type="info" style={{ marginTop: variables.spacingLarge }}>
            <IconInfo size="small" /> This is a private tracker. Registration is by invite only.
          </Message>
        </LoginCard>
      </PageContainer>
    </SplunkThemeProvider>
  );
};

export default LoginPage;
