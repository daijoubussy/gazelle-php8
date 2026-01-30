/**
 * TorrentCard Component
 *
 * Displays torrent information in a card format
 * Uses Splunk UI components with Gazelle-specific styling
 */

import React from 'react';
import styled from 'styled-components';
import { Link } from 'react-router-dom';
import { variables } from '@splunk/themes';
import Badge from '@splunk/react-ui/Badge';
import Tooltip from '@splunk/react-ui/Tooltip';

import {
  IconTorrent,
  IconDownload,
  IconUpload,
  IconSnatched,
  IconFreeleech,
  IconNeutralLeech,
  IconClock,
  IconUser,
  IconFile,
  IconFlag,
} from '@components/Icons';
import { type Torrent, type TorrentGroup } from '@types';
import { formatBytes, formatRelativeTime, formatNumber } from '@utils/format';

// Styled components
const CardContainer = styled.div`
  display: flex;
  gap: ${variables.spacing};
  padding: ${variables.spacing};
  background-color: ${variables.backgroundColorSection};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  transition: border-color 0.15s ease;

  &:hover {
    border-color: ${variables.brandColor};
  }
`;

const TorrentImage = styled.img`
  width: 120px;
  height: 170px;
  object-fit: cover;
  border-radius: ${variables.borderRadiusSmall};
  background-color: ${variables.backgroundColorHover};
`;

const TorrentImagePlaceholder = styled.div`
  width: 120px;
  height: 170px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: ${variables.backgroundColorHover};
  border-radius: ${variables.borderRadiusSmall};
  color: ${variables.textGray};
`;

const TorrentInfo = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingHalf};
`;

const TorrentTitle = styled(Link)`
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
  text-decoration: none;

  &:hover {
    color: ${variables.brandColor};
    text-decoration: underline;
  }
`;

const TorrentMeta = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: ${variables.spacingHalf};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const MetaItem = styled.span`
  display: inline-flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
`;

const TagList = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: ${variables.spacingQuarter};
`;

const TagBadge = styled(Link)`
  padding: ${variables.spacingQuarter} ${variables.spacingHalf};
  background-color: ${variables.backgroundColorHover};
  border-radius: ${variables.borderRadiusSmall};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
  text-decoration: none;

  &:hover {
    background-color: ${variables.brandColorL10};
    color: ${variables.brandColor};
  }
`;

const TorrentVariants = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
  margin-top: ${variables.spacingHalf};
`;

const VariantRow = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
  padding: ${variables.spacingQuarter} ${variables.spacingHalf};
  background-color: ${variables.backgroundColorPage};
  border-radius: ${variables.borderRadiusSmall};
  font-size: ${variables.fontSizeSmall};
`;

const VariantInfo = styled.span`
  color: ${variables.textGray};
`;

const VariantStats = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
  margin-left: auto;
`;

const StatItem = styled.span<{ $color?: string }>`
  display: inline-flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  color: ${({ $color }) => $color || variables.textGray};
`;

const DownloadButton = styled(Link)`
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: ${variables.spacingQuarter} ${variables.spacingHalf};
  background-color: ${variables.brandColor};
  color: ${variables.white};
  border-radius: ${variables.borderRadiusSmall};
  text-decoration: none;
  font-size: ${variables.fontSizeSmall};

  &:hover {
    background-color: ${variables.brandColorD10};
  }
`;

const SpecialBadge = styled.span<{ $type: 'freeleech' | 'neutralleech' | 'reported' }>`
  display: inline-flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  padding: ${variables.spacingQuarter} ${variables.spacingHalf};
  border-radius: ${variables.borderRadiusSmall};
  font-size: ${variables.fontSizeSmall};
  font-weight: ${variables.fontWeightSemiBold};

  ${({ $type }) => {
    switch ($type) {
      case 'freeleech':
        return `
          background-color: #4CAF5020;
          color: #4CAF50;
        `;
      case 'neutralleech':
        return `
          background-color: #2196F320;
          color: #2196F3;
        `;
      case 'reported':
        return `
          background-color: #F4433620;
          color: #F44336;
        `;
    }
  }}
`;

// Props
export interface TorrentCardProps {
  group: TorrentGroup;
  showVariants?: boolean;
  showImage?: boolean;
  compact?: boolean;
}

/**
 * TorrentCard Component
 */
const TorrentCard: React.FC<TorrentCardProps> = ({
  group,
  showVariants = true,
  showImage = true,
  compact = false,
}) => {
  const displayedTorrents = showVariants ? group.torrents : group.torrents.slice(0, 1);

  return (
    <CardContainer>
      {showImage && (
        group.image ? (
          <TorrentImage src={group.image} alt={group.name} loading="lazy" />
        ) : (
          <TorrentImagePlaceholder>
            <IconTorrent size="xlarge" />
          </TorrentImagePlaceholder>
        )
      )}

      <TorrentInfo>
        <TorrentTitle to={`/torrents/${group.id}`}>{group.name}</TorrentTitle>

        {group.artists && group.artists.length > 0 && (
          <TorrentMeta>
            {group.artists.map((artist, index) => (
              <React.Fragment key={artist.id}>
                <Link to={`/artist/${artist.id}`}>{artist.name}</Link>
                {index < group.artists!.length - 1 && ', '}
              </React.Fragment>
            ))}
          </TorrentMeta>
        )}

        <TorrentMeta>
          {group.year && <MetaItem>{group.year}</MetaItem>}
        </TorrentMeta>

        {group.tags && group.tags.length > 0 && (
          <TagList>
            {group.tags.slice(0, 8).map((tag) => (
              <TagBadge key={tag.id} to={`/torrents?tags=${tag.name}`}>
                {tag.name}
              </TagBadge>
            ))}
            {group.tags.length > 8 && (
              <TagBadge to={`/torrents/${group.id}`}>+{group.tags.length - 8}</TagBadge>
            )}
          </TagList>
        )}

        <TorrentVariants>
          {displayedTorrents.map((torrent) => (
            <VariantRow key={torrent.id}>
              <VariantInfo>
                {[torrent.resolution, torrent.media, torrent.format, torrent.encoding]
                  .filter(Boolean)
                  .join(' / ')}
              </VariantInfo>

              <MetaItem>
                <IconFile size="small" />
                {formatBytes(torrent.size)}
              </MetaItem>

              {torrent.freeleech && (
                <SpecialBadge $type="freeleech">
                  <IconFreeleech size="small" />
                  FL
                </SpecialBadge>
              )}

              {torrent.neutralLeech && (
                <SpecialBadge $type="neutralleech">
                  <IconNeutralLeech size="small" />
                  NL
                </SpecialBadge>
              )}

              {torrent.reported && (
                <Tooltip content="Reported">
                  <SpecialBadge $type="reported">
                    <IconFlag size="small" />
                  </SpecialBadge>
                </Tooltip>
              )}

              <VariantStats>
                <Tooltip content="Seeders">
                  <StatItem $color={torrent.seeders > 0 ? '#4CAF50' : '#F44336'}>
                    <IconUpload size="small" />
                    {formatNumber(torrent.seeders)}
                  </StatItem>
                </Tooltip>

                <Tooltip content="Leechers">
                  <StatItem $color="#FF9800">
                    <IconDownload size="small" />
                    {formatNumber(torrent.leechers)}
                  </StatItem>
                </Tooltip>

                <Tooltip content="Snatches">
                  <StatItem>
                    <IconSnatched size="small" />
                    {formatNumber(torrent.snatches)}
                  </StatItem>
                </Tooltip>
              </VariantStats>

              <DownloadButton to={`/torrents/download/${torrent.id}`}>
                <IconTorrent size="small" />
              </DownloadButton>
            </VariantRow>
          ))}
        </TorrentVariants>

        <TorrentMeta>
          <Tooltip content={torrent.uploadTime}>
            <MetaItem>
              <IconClock size="small" />
              {formatRelativeTime(displayedTorrents[0]?.uploadTime || '')}
            </MetaItem>
          </Tooltip>
          <MetaItem>
            <IconUser size="small" />
            <Link to={`/user/${displayedTorrents[0]?.uploaderId}`}>
              {displayedTorrents[0]?.uploaderName}
            </Link>
          </MetaItem>
        </TorrentMeta>
      </TorrentInfo>
    </CardContainer>
  );
};

export default TorrentCard;
