/**
 * Rules Page
 *
 * Display site rules and guidelines
 */

import React from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import TabLayout from '@splunk/react-ui/TabLayout';

import Card from '@components/Card';
import { IconInfo, IconWarning } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const RuleSection = styled.div`
  margin-bottom: ${variables.spacingLarge};

  &:last-child {
    margin-bottom: 0;
  }
`;

const RuleTitle = styled.h3`
  margin: 0 0 ${variables.spacing};
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const RuleList = styled.ol`
  margin: 0;
  padding-left: ${variables.spacingLarge};
`;

const RuleItem = styled.li`
  margin-bottom: ${variables.spacingHalf};
  color: ${variables.textColor};
  line-height: 1.6;
`;

const WarningBox = styled.div`
  display: flex;
  align-items: flex-start;
  gap: ${variables.spacing};
  padding: ${variables.spacing};
  background-color: ${variables.warningColorBackground};
  border: 1px solid ${variables.warningColor};
  border-radius: ${variables.borderRadius};
  margin-bottom: ${variables.spacingLarge};
`;

const WarningIcon = styled.div`
  color: ${variables.warningColor};
  flex-shrink: 0;
`;

const WarningText = styled.div`
  color: ${variables.textColor};
`;

/**
 * Rules Page Component
 */
const RulesPage: React.FC = () => {
  return (
    <PageContainer>
      <Card title="Site Rules" subtitle="Please read and follow all rules">
        <WarningBox>
          <WarningIcon>
            <IconWarning size="medium" />
          </WarningIcon>
          <WarningText>
            Violation of these rules may result in warnings, loss of privileges, or account
            termination. Please read carefully and follow all guidelines.
          </WarningText>
        </WarningBox>

        <TabLayout defaultActivePanelId="general">
          <TabLayout.Panel label="General Rules" panelId="general">
            <RuleSection>
              <RuleTitle>Account Rules</RuleTitle>
              <RuleList>
                <RuleItem>One account per user. Multiple accounts are not allowed.</RuleItem>
                <RuleItem>Do not share your account with anyone.</RuleItem>
                <RuleItem>Keep your account secure with a strong password.</RuleItem>
                <RuleItem>Invites are a privilege. Only invite people you trust.</RuleItem>
              </RuleList>
            </RuleSection>

            <RuleSection>
              <RuleTitle>Ratio Rules</RuleTitle>
              <RuleList>
                <RuleItem>Maintain a ratio of at least 0.6 at all times.</RuleItem>
                <RuleItem>New users have a grace period to build their ratio.</RuleItem>
                <RuleItem>Cheating ratio is strictly forbidden and results in instant ban.</RuleItem>
              </RuleList>
            </RuleSection>
          </TabLayout.Panel>

          <TabLayout.Panel label="Upload Rules" panelId="upload">
            <RuleSection>
              <RuleTitle>Content Guidelines</RuleTitle>
              <RuleList>
                <RuleItem>Only upload content that matches the site focus.</RuleItem>
                <RuleItem>Ensure proper formatting and naming conventions.</RuleItem>
                <RuleItem>Include accurate metadata and descriptions.</RuleItem>
                <RuleItem>Do not upload duplicates of existing content.</RuleItem>
              </RuleList>
            </RuleSection>

            <RuleSection>
              <RuleTitle>Quality Standards</RuleTitle>
              <RuleList>
                <RuleItem>Lossless uploads must be from legitimate sources.</RuleItem>
                <RuleItem>Transcodes must be from lossless sources only.</RuleItem>
                <RuleItem>Properly tag and organize files before uploading.</RuleItem>
              </RuleList>
            </RuleSection>
          </TabLayout.Panel>

          <TabLayout.Panel label="Forum Rules" panelId="forum">
            <RuleSection>
              <RuleTitle>Behavior Guidelines</RuleTitle>
              <RuleList>
                <RuleItem>Be respectful to other users.</RuleItem>
                <RuleItem>No harassment, hate speech, or personal attacks.</RuleItem>
                <RuleItem>Stay on topic in threads.</RuleItem>
                <RuleItem>Search before creating new threads.</RuleItem>
              </RuleList>
            </RuleSection>
          </TabLayout.Panel>
        </TabLayout>
      </Card>
    </PageContainer>
  );
};

export default RulesPage;
