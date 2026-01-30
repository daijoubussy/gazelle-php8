/**
 * Requests Page
 *
 * Browse and manage torrent requests
 */

import React from 'react';
import { useParams } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';

import Card from '@components/Card';
import { IconRequest, IconAdd } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const HeaderActions = styled.div`
  display: flex;
  justify-content: flex-end;
`;

const RequestList = styled.div`
  display: flex;
  flex-direction: column;
`;

const RequestItem = styled.div`
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: ${variables.spacing};
  padding: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};
  align-items: center;

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const RequestInfo = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
`;

const RequestTitle = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const RequestMeta = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const Bounty = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.successColor};
`;

/**
 * Requests Page Component
 */
const RequestsPage: React.FC = () => {
  const { requestId } = useParams<{ requestId?: string }>();

  const requests = [
    { id: 1, title: 'Rare Album Request', artist: 'Artist Name', bounty: '500 MB', votes: 12, created: '2 days ago' },
    { id: 2, title: 'FLAC version needed', artist: 'Another Artist', bounty: '1 GB', votes: 8, created: '1 week ago' },
    { id: 3, title: 'Looking for discography', artist: 'Band Name', bounty: '2 GB', votes: 25, created: '3 days ago' },
  ];

  if (requestId) {
    return (
      <PageContainer>
        <Card title="Request Details">
          <p>Viewing request {requestId}</p>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer>
      <Card
        title="Requests"
        subtitle="Browse and fulfill torrent requests"
        actions={<Button icon={<IconAdd />} appearance="primary" label="New Request" />}
      >
        <RequestList>
          {requests.map((request) => (
            <RequestItem key={request.id}>
              <RequestInfo>
                <RequestTitle>{request.title}</RequestTitle>
                <RequestMeta>
                  {request.artist} - {request.votes} votes - Created {request.created}
                </RequestMeta>
              </RequestInfo>
              <Bounty>{request.bounty}</Bounty>
              <Button size="small" label="Fill" />
            </RequestItem>
          ))}
        </RequestList>
      </Card>
    </PageContainer>
  );
};

export default RequestsPage;
