/**
 * Settings Page
 *
 * User settings and preferences
 */

import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import Text from '@splunk/react-ui/Text';
import Select from '@splunk/react-ui/Select';
import Switch from '@splunk/react-ui/Switch';

import Card from '@components/Card';
import { IconSettings, IconUser, IconLock, IconNotification, IconTheme } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const SettingsLayout = styled.div`
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: ${variables.spacingLarge};

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const SettingsNav = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
`;

const NavLink = styled(Link)<{ $active?: boolean }>`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  padding: ${variables.spacingHalf} ${variables.spacing};
  color: ${({ $active }) => ($active ? variables.brandColor : variables.textColor)};
  background-color: ${({ $active }) =>
    $active ? variables.backgroundColorHover : 'transparent'};
  text-decoration: none;
  border-radius: ${variables.borderRadius};

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const SettingsContent = styled.div`
  flex: 1;
`;

const FormSection = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
  margin-bottom: ${variables.spacingLarge};

  &:last-child {
    margin-bottom: 0;
  }
`;

const SectionTitle = styled.h3`
  margin: 0 0 ${variables.spacing};
  padding-bottom: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const FormGroup = styled.div`
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: ${variables.spacing};
  align-items: center;

  @media (max-width: 600px) {
    grid-template-columns: 1fr;
  }
`;

const Label = styled.label`
  font-size: ${variables.fontSize};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const HelpText = styled.span`
  grid-column: 2;
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};

  @media (max-width: 600px) {
    grid-column: 1;
  }
`;

const FormActions = styled.div`
  display: flex;
  justify-content: flex-end;
  gap: ${variables.spacing};
  padding-top: ${variables.spacingLarge};
  border-top: 1px solid ${variables.borderColor};
`;

/**
 * Settings Page Component
 */
const SettingsPage: React.FC = () => {
  const { section = 'profile' } = useParams<{ section?: string }>();

  const [email, setEmail] = useState('user@example.com');
  const [theme, setTheme] = useState('dark');
  const [notifications, setNotifications] = useState(true);
  const [emailNotifications, setEmailNotifications] = useState(false);

  const navItems = [
    { id: 'profile', label: 'Profile', icon: <IconUser size="small" /> },
    { id: 'security', label: 'Security', icon: <IconLock size="small" /> },
    { id: 'notifications', label: 'Notifications', icon: <IconNotification size="small" /> },
    { id: 'appearance', label: 'Appearance', icon: <IconTheme size="small" /> },
  ];

  const renderContent = () => {
    switch (section) {
      case 'security':
        return (
          <Card title="Security Settings">
            <FormSection>
              <SectionTitle>Password</SectionTitle>
              <FormGroup>
                <Label>Current Password</Label>
                <Text type="password" placeholder="Enter current password" />
              </FormGroup>
              <FormGroup>
                <Label>New Password</Label>
                <Text type="password" placeholder="Enter new password" />
              </FormGroup>
              <FormGroup>
                <Label>Confirm Password</Label>
                <Text type="password" placeholder="Confirm new password" />
              </FormGroup>
            </FormSection>
            <FormActions>
              <Button appearance="primary" label="Update Password" />
            </FormActions>
          </Card>
        );

      case 'notifications':
        return (
          <Card title="Notification Settings">
            <FormSection>
              <SectionTitle>Notifications</SectionTitle>
              <FormGroup>
                <Label>Site Notifications</Label>
                <Switch
                  selected={notifications}
                  onClick={() => setNotifications(!notifications)}
                />
              </FormGroup>
              <HelpText>Receive notifications for messages, requests, and updates</HelpText>
              <FormGroup>
                <Label>Email Notifications</Label>
                <Switch
                  selected={emailNotifications}
                  onClick={() => setEmailNotifications(!emailNotifications)}
                />
              </FormGroup>
              <HelpText>Receive important notifications via email</HelpText>
            </FormSection>
            <FormActions>
              <Button appearance="primary" label="Save Changes" />
            </FormActions>
          </Card>
        );

      case 'appearance':
        return (
          <Card title="Appearance Settings">
            <FormSection>
              <SectionTitle>Theme</SectionTitle>
              <FormGroup>
                <Label>Color Scheme</Label>
                <Select value={theme} onChange={(e, { value }) => setTheme(value as string)}>
                  <Select.Option label="Dark" value="dark" />
                  <Select.Option label="Light" value="light" />
                  <Select.Option label="System" value="system" />
                </Select>
              </FormGroup>
            </FormSection>
            <FormActions>
              <Button appearance="primary" label="Save Changes" />
            </FormActions>
          </Card>
        );

      default:
        return (
          <Card title="Profile Settings">
            <FormSection>
              <SectionTitle>Account Information</SectionTitle>
              <FormGroup>
                <Label>Email Address</Label>
                <Text
                  value={email}
                  onChange={(e, { value }) => setEmail(value)}
                  type="email"
                />
              </FormGroup>
              <FormGroup>
                <Label>IRC Key</Label>
                <Text placeholder="Generate or enter IRC key" />
              </FormGroup>
              <HelpText>Used for IRC authentication</HelpText>
            </FormSection>
            <FormActions>
              <Button appearance="primary" label="Save Changes" />
            </FormActions>
          </Card>
        );
    }
  };

  return (
    <PageContainer>
      <SettingsLayout>
        <Card padding="small">
          <SettingsNav>
            {navItems.map((item) => (
              <NavLink
                key={item.id}
                to={`/settings/${item.id}`}
                $active={section === item.id}
              >
                {item.icon}
                {item.label}
              </NavLink>
            ))}
          </SettingsNav>
        </Card>

        <SettingsContent>{renderContent()}</SettingsContent>
      </SettingsLayout>
    </PageContainer>
  );
};

export default SettingsPage;
