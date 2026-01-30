/**
 * Inbox Page
 *
 * Private messaging system
 */

import React, { useState } from 'react';
import { useParams } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import Text from '@splunk/react-ui/Text';
import TextArea from '@splunk/react-ui/TextArea';

import Card from '@components/Card';
import { IconMail, IconSend, IconTrash, IconUser } from '@components/Icons';
import { formatTimeAgo } from '@utils/format';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const InboxLayout = styled.div`
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: ${variables.spacingLarge};
  min-height: 500px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const ConversationList = styled.div`
  display: flex;
  flex-direction: column;
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  overflow: hidden;
`;

const ConversationItem = styled.div<{ $active?: boolean; $unread?: boolean }>`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
  padding: ${variables.spacing};
  background-color: ${({ $active }) =>
    $active ? variables.backgroundColorHover : 'transparent'};
  border-bottom: 1px solid ${variables.borderColor};
  cursor: pointer;
  font-weight: ${({ $unread }) => ($unread ? variables.fontWeightSemiBold : 'normal')};

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const ConversationHeader = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
`;

const ConversationSender = styled.span`
  color: ${variables.textColor};
`;

const ConversationTime = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
  font-weight: normal;
`;

const ConversationSubject = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
`;

const MessagePane = styled.div`
  display: flex;
  flex-direction: column;
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  overflow: hidden;
`;

const MessageHeader = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: ${variables.spacing};
  background-color: ${variables.backgroundColorPage};
  border-bottom: 1px solid ${variables.borderColor};
`;

const MessageSubject = styled.h3`
  margin: 0;
  font-size: ${variables.fontSize};
  font-weight: ${variables.fontWeightSemiBold};
`;

const MessageActions = styled.div`
  display: flex;
  gap: ${variables.spacingHalf};
`;

const MessageBody = styled.div`
  flex: 1;
  padding: ${variables.spacing};
  overflow-y: auto;
`;

const Message = styled.div`
  display: flex;
  gap: ${variables.spacing};
  margin-bottom: ${variables.spacingLarge};
`;

const MessageAvatar = styled.div`
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: ${variables.backgroundColorHover};
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
`;

const MessageContent = styled.div`
  flex: 1;
`;

const MessageMeta = styled.div`
  display: flex;
  gap: ${variables.spacing};
  margin-bottom: ${variables.spacingHalf};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const MessageText = styled.div`
  color: ${variables.textColor};
  line-height: 1.6;
`;

const ReplyBox = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
  padding: ${variables.spacing};
  background-color: ${variables.backgroundColorPage};
  border-top: 1px solid ${variables.borderColor};
`;

/**
 * Inbox Page Component
 */
const InboxPage: React.FC = () => {
  const { conversationId } = useParams<{ conversationId?: string }>();
  const [replyText, setReplyText] = useState('');

  const conversations = [
    { id: 1, sender: 'Admin', subject: 'Welcome to the site!', time: '2 hours ago', unread: true },
    { id: 2, sender: 'User123', subject: 'Question about upload', time: '1 day ago', unread: false },
    { id: 3, sender: 'Staff', subject: 'Your request was filled', time: '3 days ago', unread: false },
  ];

  const selectedConversation = conversationId
    ? conversations.find((c) => c.id === Number(conversationId))
    : conversations[0];

  return (
    <PageContainer>
      <Card
        title="Inbox"
        actions={<Button icon={<IconSend />} appearance="primary" label="New Message" />}
      >
        <InboxLayout>
          <ConversationList>
            {conversations.map((conv) => (
              <ConversationItem
                key={conv.id}
                $active={selectedConversation?.id === conv.id}
                $unread={conv.unread}
              >
                <ConversationHeader>
                  <ConversationSender>{conv.sender}</ConversationSender>
                  <ConversationTime>{conv.time}</ConversationTime>
                </ConversationHeader>
                <ConversationSubject>{conv.subject}</ConversationSubject>
              </ConversationItem>
            ))}
          </ConversationList>

          <MessagePane>
            <MessageHeader>
              <MessageSubject>{selectedConversation?.subject}</MessageSubject>
              <MessageActions>
                <Button icon={<IconTrash />} size="small" />
              </MessageActions>
            </MessageHeader>

            <MessageBody>
              <Message>
                <MessageAvatar>
                  <IconUser size="small" />
                </MessageAvatar>
                <MessageContent>
                  <MessageMeta>
                    <span>{selectedConversation?.sender}</span>
                    <span>{selectedConversation?.time}</span>
                  </MessageMeta>
                  <MessageText>
                    This is the message content. Welcome to the private tracker!
                    Please make sure to read the rules and maintain your ratio.
                  </MessageText>
                </MessageContent>
              </Message>
            </MessageBody>

            <ReplyBox>
              <TextArea
                value={replyText}
                onChange={(e, { value }) => setReplyText(value)}
                placeholder="Type your reply..."
                rowsMin={3}
              />
              <Button
                appearance="primary"
                icon={<IconSend />}
                label="Send Reply"
                disabled={!replyText.trim()}
              />
            </ReplyBox>
          </MessagePane>
        </InboxLayout>
      </Card>
    </PageContainer>
  );
};

export default InboxPage;
