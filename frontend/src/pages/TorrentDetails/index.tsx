/**
 * Torrent Details Page
 *
 * Detailed view of a torrent group with file listings
 */

import React from 'react';
import { useParams } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import Tabs from '@splunk/react-ui/Tabs';
import TabLayout from '@splunk/react-ui/TabLayout';

import Card from '@components/Card';
import {
  IconDownload,
  IconSeeders,
  IconLeechers,
  IconFile,
  IconBookmark,
  IconReport,
  IconEdit,
} from '@components/Icons';
import { formatSize, formatTimeAgo } from '@utils/format';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const HeaderSection = styled.div`
  display: grid;
  grid-template-columns: auto 1fr;
  gap: ${variables.spacingLarge};

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const CoverImage = styled.div`
  width: 200px;
  height: 200px;
  background-color: ${variables.backgroundColorHover};
  border-radius: ${variables.borderRadius};
  display: flex;
  align-items: center;
  justify-content: center;
  color: ${variables.textGray};
`;

const TorrentInfo = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const TorrentTitle = styled.h1`
  margin: 0;
  font-size: ${variables.fontSizeXXLarge};
  font-weight: ${variables.fontWeightBold};
  color: ${variables.textColor};
`;

const TorrentArtist = styled.h2`
  margin: 0;
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightNormal};
  color: ${variables.textGray};
`;

const MetaList = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: ${variables.spacing};
`;

const MetaItem = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const ActionButtons = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: ${variables.spacingHalf};
  margin-top: auto;
`;

const EditionSection = styled.div`
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  overflow: hidden;
`;

const EditionHeader = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: ${variables.spacing};
  background-color: ${variables.backgroundColorHover};
  border-bottom: 1px solid ${variables.borderColor};
`;

const EditionTitle = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
`;

const TorrentRow = styled.div`
  display: grid;
  grid-template-columns: 1fr auto auto auto auto;
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

const TorrentFormat = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textColor};
`;

const StatBadge = styled.span<{ $type?: 'seeders' | 'leechers' }>`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  font-size: ${variables.fontSizeSmall};
  color: ${({ $type }) =>
    $type === 'seeders'
      ? variables.successColor
      : $type === 'leechers'
      ? variables.errorColor
      : variables.textGray};
`;

const FileList = styled.div`
  font-family: monospace;
  font-size: ${variables.fontSizeSmall};
`;

const FileItem = styled.div`
  display: flex;
  justify-content: space-between;
  padding: ${variables.spacingQuarter} 0;
  border-bottom: 1px solid ${variables.borderColor};

  &:last-child {
    border-bottom: none;
  }
`;

/**
 * Torrent Details Page Component
 */
const TorrentDetailsPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();

  // Placeholder data
  const torrentGroup = {
    id: Number(id),
    name: 'Example Album',
    artist: 'Example Artist',
    year: 2024,
    category: 'Music',
    tags: ['electronic', 'ambient', 'experimental'],
    description: 'An amazing album with great production quality.',
    editions: [
      {
        id: 1,
        title: 'Original Release',
        year: 2024,
        label: 'Example Records',
        torrents: [
          {
            id: 1,
            format: 'FLAC',
            bitrate: 'Lossless',
            size: 524288000,
            seeders: 42,
            leechers: 5,
            snatches: 128,
            uploadedAt: new Date(Date.now() - 86400000).toISOString(),
            files: [
              { name: '01 - Track One.flac', size: 52428800 },
              { name: '02 - Track Two.flac', size: 48576000 },
              { name: '03 - Track Three.flac', size: 55283200 },
            ],
          },
          {
            id: 2,
            format: 'MP3',
            bitrate: '320',
            size: 104857600,
            seeders: 15,
            leechers: 2,
            snatches: 45,
            uploadedAt: new Date(Date.now() - 172800000).toISOString(),
            files: [
              { name: '01 - Track One.mp3', size: 10485760 },
              { name: '02 - Track Two.mp3', size: 9728000 },
              { name: '03 - Track Three.mp3', size: 11059200 },
            ],
          },
        ],
      },
    ],
  };

  return (
    <PageContainer>
      <Card>
        <HeaderSection>
          <CoverImage>No Image</CoverImage>
          <TorrentInfo>
            <TorrentArtist>{torrentGroup.artist}</TorrentArtist>
            <TorrentTitle>{torrentGroup.name}</TorrentTitle>
            <MetaList>
              <MetaItem>Year: {torrentGroup.year}</MetaItem>
              <MetaItem>Category: {torrentGroup.category}</MetaItem>
              <MetaItem>Tags: {torrentGroup.tags.join(', ')}</MetaItem>
            </MetaList>
            <ActionButtons>
              <Button icon={<IconBookmark />} label="Bookmark" />
              <Button icon={<IconReport />} label="Report" />
              <Button icon={<IconEdit />} label="Edit" />
            </ActionButtons>
          </TorrentInfo>
        </HeaderSection>
      </Card>

      <TabLayout defaultActivePanelId="torrents">
        <TabLayout.Panel label="Torrents" panelId="torrents">
          {torrentGroup.editions.map((edition) => (
            <EditionSection key={edition.id}>
              <EditionHeader>
                <EditionTitle>
                  {edition.title} ({edition.year}) - {edition.label}
                </EditionTitle>
              </EditionHeader>
              {edition.torrents.map((torrent) => (
                <TorrentRow key={torrent.id}>
                  <TorrentFormat>
                    {torrent.format} / {torrent.bitrate}
                  </TorrentFormat>
                  <span>{formatSize(torrent.size)}</span>
                  <StatBadge $type="seeders">
                    <IconSeeders size="small" /> {torrent.seeders}
                  </StatBadge>
                  <StatBadge $type="leechers">
                    <IconLeechers size="small" /> {torrent.leechers}
                  </StatBadge>
                  <Button
                    appearance="primary"
                    size="small"
                    icon={<IconDownload />}
                    label="DL"
                  />
                </TorrentRow>
              ))}
            </EditionSection>
          ))}
        </TabLayout.Panel>

        <TabLayout.Panel label="Description" panelId="description">
          <Card>
            <p>{torrentGroup.description}</p>
          </Card>
        </TabLayout.Panel>

        <TabLayout.Panel label="Files" panelId="files">
          <Card>
            <FileList>
              {torrentGroup.editions[0]?.torrents[0]?.files.map((file, index) => (
                <FileItem key={index}>
                  <span>
                    <IconFile size="small" /> {file.name}
                  </span>
                  <span>{formatSize(file.size)}</span>
                </FileItem>
              ))}
            </FileList>
          </Card>
        </TabLayout.Panel>
      </TabLayout>
    </PageContainer>
  );
};

export default TorrentDetailsPage;
