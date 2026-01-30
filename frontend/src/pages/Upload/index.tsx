/**
 * Upload Page
 *
 * Torrent upload form
 */

import React, { useState } from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import Text from '@splunk/react-ui/Text';
import TextArea from '@splunk/react-ui/TextArea';
import Select from '@splunk/react-ui/Select';
import File from '@splunk/react-ui/File';

import Card from '@components/Card';
import { IconUpload, IconInfo } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const Form = styled.form`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const FormSection = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const SectionTitle = styled.h3`
  margin: 0;
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const FormGroup = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
`;

const Label = styled.label`
  font-size: ${variables.fontSizeSmall};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const HelpText = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const FormRow = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: ${variables.spacing};
`;

const FormActions = styled.div`
  display: flex;
  justify-content: flex-end;
  gap: ${variables.spacing};
  padding-top: ${variables.spacing};
  border-top: 1px solid ${variables.borderColor};
`;

/**
 * Upload Page Component
 */
const UploadPage: React.FC = () => {
  const [category, setCategory] = useState('');
  const [title, setTitle] = useState('');
  const [artist, setArtist] = useState('');
  const [year, setYear] = useState('');
  const [format, setFormat] = useState('');
  const [bitrate, setBitrate] = useState('');
  const [description, setDescription] = useState('');
  const [torrentFile, setTorrentFile] = useState<File | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Handle upload
    console.log('Uploading...');
  };

  return (
    <PageContainer>
      <Card title="Upload Torrent" subtitle="Share content with the community">
        <Form onSubmit={handleSubmit}>
          <FormSection>
            <SectionTitle>Torrent File</SectionTitle>
            <FormGroup>
              <Label>Select .torrent file</Label>
              <File
                accept=".torrent"
                onChange={(e, { files }) => setTorrentFile(files?.[0] || null)}
              />
              <HelpText>
                <IconInfo size="small" /> Maximum file size: 10MB
              </HelpText>
            </FormGroup>
          </FormSection>

          <FormSection>
            <SectionTitle>Category & Type</SectionTitle>
            <FormRow>
              <FormGroup>
                <Label>Category</Label>
                <Select
                  value={category}
                  onChange={(e, { value }) => setCategory(value as string)}
                >
                  <Select.Option label="Select category..." value="" />
                  <Select.Option label="Music" value="music" />
                  <Select.Option label="Applications" value="apps" />
                  <Select.Option label="E-Books" value="ebooks" />
                  <Select.Option label="Audiobooks" value="audiobooks" />
                </Select>
              </FormGroup>
              <FormGroup>
                <Label>Format</Label>
                <Select
                  value={format}
                  onChange={(e, { value }) => setFormat(value as string)}
                >
                  <Select.Option label="Select format..." value="" />
                  <Select.Option label="FLAC" value="flac" />
                  <Select.Option label="MP3" value="mp3" />
                  <Select.Option label="AAC" value="aac" />
                  <Select.Option label="Ogg Vorbis" value="ogg" />
                </Select>
              </FormGroup>
              <FormGroup>
                <Label>Bitrate</Label>
                <Select
                  value={bitrate}
                  onChange={(e, { value }) => setBitrate(value as string)}
                >
                  <Select.Option label="Select bitrate..." value="" />
                  <Select.Option label="Lossless" value="lossless" />
                  <Select.Option label="320" value="320" />
                  <Select.Option label="V0 (VBR)" value="v0" />
                  <Select.Option label="V2 (VBR)" value="v2" />
                </Select>
              </FormGroup>
            </FormRow>
          </FormSection>

          <FormSection>
            <SectionTitle>Release Information</SectionTitle>
            <FormRow>
              <FormGroup>
                <Label>Artist</Label>
                <Text
                  value={artist}
                  onChange={(e, { value }) => setArtist(value)}
                  placeholder="Enter artist name"
                />
              </FormGroup>
              <FormGroup>
                <Label>Title</Label>
                <Text
                  value={title}
                  onChange={(e, { value }) => setTitle(value)}
                  placeholder="Enter release title"
                />
              </FormGroup>
              <FormGroup>
                <Label>Year</Label>
                <Text
                  value={year}
                  onChange={(e, { value }) => setYear(value)}
                  placeholder="YYYY"
                />
              </FormGroup>
            </FormRow>
          </FormSection>

          <FormSection>
            <SectionTitle>Description</SectionTitle>
            <FormGroup>
              <Label>Release Description</Label>
              <TextArea
                value={description}
                onChange={(e, { value }) => setDescription(value)}
                placeholder="Enter a detailed description of the release..."
                rowsMin={6}
              />
              <HelpText>BBCode formatting is supported</HelpText>
            </FormGroup>
          </FormSection>

          <FormActions>
            <Button type="button" label="Cancel" />
            <Button
              type="submit"
              appearance="primary"
              icon={<IconUpload />}
              label="Upload"
            />
          </FormActions>
        </Form>
      </Card>
    </PageContainer>
  );
};

export default UploadPage;
