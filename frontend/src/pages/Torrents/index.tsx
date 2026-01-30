/**
 * Torrents Page
 *
 * Browse and search torrents
 */

import React, { useState } from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Text from '@splunk/react-ui/Text';
import Select from '@splunk/react-ui/Select';
import Button from '@splunk/react-ui/Button';

import Card from '@components/Card';
import DataTable from '@components/DataTable';
import TorrentCard from '@components/TorrentCard';
import { IconSearch, IconFilter, IconGrid, IconList } from '@components/Icons';
import type { Torrent } from '@/types';
import { formatSize, formatTimeAgo } from '@utils/format';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const SearchBar = styled.div`
  display: flex;
  gap: ${variables.spacing};
  flex-wrap: wrap;
`;

const SearchInput = styled.div`
  flex: 1;
  min-width: 300px;
`;

const FilterGroup = styled.div`
  display: flex;
  gap: ${variables.spacingHalf};
`;

const ViewToggle = styled.div`
  display: flex;
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  overflow: hidden;
`;

const ViewButton = styled.button<{ $active?: boolean }>`
  display: flex;
  align-items: center;
  justify-content: center;
  padding: ${variables.spacingHalf};
  background-color: ${({ $active }) =>
    $active ? variables.backgroundColorHover : variables.backgroundColorSection};
  border: none;
  cursor: pointer;
  color: ${({ $active }) => ($active ? variables.brandColor : variables.textGray)};

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const TorrentGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: ${variables.spacing};
`;

/**
 * Torrents Page Component
 */
const TorrentsPage: React.FC = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [category, setCategory] = useState('all');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');

  // Placeholder data
  const torrents: Torrent[] = [
    {
      id: 1,
      name: 'Example Album - Discography (FLAC)',
      category: 'Music',
      size: 2147483648,
      seeders: 42,
      leechers: 5,
      snatches: 128,
      uploadedAt: new Date(Date.now() - 3600000).toISOString(),
      uploaderId: 1,
      uploaderName: 'Uploader1',
      isFreeleech: true,
      isNeutralLeech: false,
      groupId: 1,
    },
    {
      id: 2,
      name: 'Another Release - Album Name (MP3 320)',
      category: 'Music',
      size: 104857600,
      seeders: 15,
      leechers: 2,
      snatches: 45,
      uploadedAt: new Date(Date.now() - 7200000).toISOString(),
      uploaderId: 2,
      uploaderName: 'Uploader2',
      isFreeleech: false,
      isNeutralLeech: false,
      groupId: 2,
    },
  ];

  const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'size', label: 'Size', sortable: true, render: (t: Torrent) => formatSize(t.size) },
    { key: 'seeders', label: 'SE', sortable: true },
    { key: 'leechers', label: 'LE', sortable: true },
    { key: 'snatches', label: 'SN', sortable: true },
    { key: 'uploadedAt', label: 'Uploaded', sortable: true, render: (t: Torrent) => formatTimeAgo(t.uploadedAt) },
  ];

  return (
    <PageContainer>
      <Card title="Browse Torrents">
        <SearchBar>
          <SearchInput>
            <Text
              value={searchQuery}
              onChange={(e, { value }) => setSearchQuery(value)}
              placeholder="Search torrents..."
              startAdornment={<IconSearch size="small" />}
            />
          </SearchInput>

          <FilterGroup>
            <Select
              value={category}
              onChange={(e, { value }) => setCategory(value as string)}
            >
              <Select.Option label="All Categories" value="all" />
              <Select.Option label="Music" value="music" />
              <Select.Option label="Applications" value="apps" />
              <Select.Option label="E-Books" value="ebooks" />
            </Select>

            <Button icon={<IconFilter />} label="Filters" />
          </FilterGroup>

          <ViewToggle>
            <ViewButton
              $active={viewMode === 'list'}
              onClick={() => setViewMode('list')}
            >
              <IconList size="small" />
            </ViewButton>
            <ViewButton
              $active={viewMode === 'grid'}
              onClick={() => setViewMode('grid')}
            >
              <IconGrid size="small" />
            </ViewButton>
          </ViewToggle>
        </SearchBar>
      </Card>

      {viewMode === 'list' ? (
        <DataTable
          data={torrents}
          columns={columns}
          pageSize={25}
          sortable
        />
      ) : (
        <TorrentGrid>
          {torrents.map((torrent) => (
            <TorrentCard key={torrent.id} torrent={torrent} />
          ))}
        </TorrentGrid>
      )}
    </PageContainer>
  );
};

export default TorrentsPage;
