/**
 * DataTable Component
 *
 * Wrapper around Splunk UI Table component with Gazelle-specific features
 * Supports sorting, pagination, selection, and custom cell rendering
 */

import React, { useState, useMemo, useCallback } from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Table from '@splunk/react-ui/Table';
import Paginator from '@splunk/react-ui/Paginator';
import Switch from '@splunk/react-ui/Switch';
import Select from '@splunk/react-ui/Select';
import Button from '@splunk/react-ui/Button';

import { IconSort, IconChevronUp, IconChevronDown, IconRefresh } from '@components/Icons';
import { type TableColumn } from '@types';

// Styled components
const TableContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const TableHeader = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: ${variables.spacingHalf} 0;
`;

const TableTitle = styled.h3`
  margin: 0;
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const TableControls = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
`;

const TableFooter = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: ${variables.spacingHalf} 0;
  border-top: 1px solid ${variables.borderColor};
`;

const PageInfo = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const RowsPerPageSelect = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const EmptyState = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: ${variables.spacingXLarge};
  text-align: center;
  color: ${variables.textGray};
`;

const SortIcon = styled.span<{ $active: boolean; $direction: 'asc' | 'desc' }>`
  display: inline-flex;
  align-items: center;
  margin-left: ${variables.spacingQuarter};
  opacity: ${({ $active }) => ($active ? 1 : 0.3)};
`;

// Sort direction type
type SortDirection = 'asc' | 'desc';

// Props
export interface DataTableProps<T> {
  data: T[];
  columns: TableColumn<T>[];
  title?: string;
  loading?: boolean;
  selectable?: boolean;
  onSelectionChange?: (selectedIds: Set<string | number>) => void;
  rowKey?: keyof T | ((row: T) => string | number);
  defaultSort?: { key: string; direction: SortDirection };
  paginated?: boolean;
  pageSize?: number;
  pageSizeOptions?: number[];
  onRefresh?: () => void;
  emptyMessage?: string;
  striped?: boolean;
  compact?: boolean;
}

/**
 * DataTable Component
 */
function DataTable<T extends Record<string, unknown>>({
  data,
  columns,
  title,
  loading = false,
  selectable = false,
  onSelectionChange,
  rowKey = 'id' as keyof T,
  defaultSort,
  paginated = true,
  pageSize: initialPageSize = 25,
  pageSizeOptions = [10, 25, 50, 100],
  onRefresh,
  emptyMessage = 'No data available',
  striped = true,
  compact = false,
}: DataTableProps<T>): React.ReactElement {
  // State
  const [sortKey, setSortKey] = useState<string | null>(defaultSort?.key || null);
  const [sortDirection, setSortDirection] = useState<SortDirection>(
    defaultSort?.direction || 'asc'
  );
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(initialPageSize);
  const [selectedIds, setSelectedIds] = useState<Set<string | number>>(new Set());

  // Get row key value
  const getRowKey = useCallback(
    (row: T): string | number => {
      if (typeof rowKey === 'function') {
        return rowKey(row);
      }
      return row[rowKey] as string | number;
    },
    [rowKey]
  );

  // Sorted data
  const sortedData = useMemo(() => {
    if (!sortKey) return data;

    const column = columns.find((col) => col.key === sortKey);
    if (!column) return data;

    return [...data].sort((a, b) => {
      const aValue = a[sortKey as keyof T];
      const bValue = b[sortKey as keyof T];

      if (aValue === bValue) return 0;
      if (aValue === null || aValue === undefined) return 1;
      if (bValue === null || bValue === undefined) return -1;

      const comparison = aValue < bValue ? -1 : 1;
      return sortDirection === 'asc' ? comparison : -comparison;
    });
  }, [data, sortKey, sortDirection, columns]);

  // Paginated data
  const paginatedData = useMemo(() => {
    if (!paginated) return sortedData;

    const startIndex = (currentPage - 1) * pageSize;
    return sortedData.slice(startIndex, startIndex + pageSize);
  }, [sortedData, currentPage, pageSize, paginated]);

  // Total pages
  const totalPages = useMemo(() => {
    return Math.ceil(sortedData.length / pageSize);
  }, [sortedData.length, pageSize]);

  // Handle sort
  const handleSort = useCallback(
    (key: string) => {
      if (sortKey === key) {
        setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'));
      } else {
        setSortKey(key);
        setSortDirection('asc');
      }
      setCurrentPage(1);
    },
    [sortKey]
  );

  // Handle page change
  const handlePageChange = useCallback(
    (_e: React.SyntheticEvent, { page }: { page: number }) => {
      setCurrentPage(page);
    },
    []
  );

  // Handle page size change
  const handlePageSizeChange = useCallback(
    (_e: React.SyntheticEvent, { value }: { value: string }) => {
      setPageSize(Number(value));
      setCurrentPage(1);
    },
    []
  );

  // Handle row selection
  const handleRowSelect = useCallback(
    (id: string | number) => {
      setSelectedIds((prev) => {
        const next = new Set(prev);
        if (next.has(id)) {
          next.delete(id);
        } else {
          next.add(id);
        }
        onSelectionChange?.(next);
        return next;
      });
    },
    [onSelectionChange]
  );

  // Handle select all
  const handleSelectAll = useCallback(() => {
    if (selectedIds.size === paginatedData.length) {
      setSelectedIds(new Set());
      onSelectionChange?.(new Set());
    } else {
      const allIds = new Set(paginatedData.map(getRowKey));
      setSelectedIds(allIds);
      onSelectionChange?.(allIds);
    }
  }, [selectedIds.size, paginatedData, getRowKey, onSelectionChange]);

  // Render cell value
  const renderCell = useCallback(
    (column: TableColumn<T>, row: T) => {
      const value = row[column.key as keyof T];

      if (column.render) {
        return column.render(value, row);
      }

      if (value === null || value === undefined) {
        return '-';
      }

      return String(value);
    },
    []
  );

  // Empty state
  if (!loading && data.length === 0) {
    return (
      <TableContainer>
        {title && (
          <TableHeader>
            <TableTitle>{title}</TableTitle>
            {onRefresh && (
              <Button appearance="secondary" onClick={onRefresh}>
                <IconRefresh size="small" />
                Refresh
              </Button>
            )}
          </TableHeader>
        )}
        <EmptyState>{emptyMessage}</EmptyState>
      </TableContainer>
    );
  }

  return (
    <TableContainer>
      {title && (
        <TableHeader>
          <TableTitle>{title}</TableTitle>
          <TableControls>
            {onRefresh && (
              <Button appearance="secondary" onClick={onRefresh} disabled={loading}>
                <IconRefresh size="small" />
              </Button>
            )}
          </TableControls>
        </TableHeader>
      )}

      <Table stripeRows={striped}>
        <Table.Head>
          {selectable && (
            <Table.HeadCell style={{ width: '40px' }}>
              <Switch
                selected={selectedIds.size === paginatedData.length && paginatedData.length > 0}
                onClick={handleSelectAll}
                appearance="checkbox"
              />
            </Table.HeadCell>
          )}
          {columns.map((column) => (
            <Table.HeadCell
              key={String(column.key)}
              style={{ width: column.width }}
              align={column.align}
              onSort={column.sortable ? () => handleSort(String(column.key)) : undefined}
              sortKey={column.sortable ? String(column.key) : undefined}
              sortDir={sortKey === column.key ? sortDirection : undefined}
            >
              {column.label}
              {column.sortable && (
                <SortIcon
                  $active={sortKey === column.key}
                  $direction={sortDirection}
                >
                  {sortKey === column.key ? (
                    sortDirection === 'asc' ? (
                      <IconChevronUp size="small" />
                    ) : (
                      <IconChevronDown size="small" />
                    )
                  ) : (
                    <IconSort size="small" />
                  )}
                </SortIcon>
              )}
            </Table.HeadCell>
          ))}
        </Table.Head>

        <Table.Body>
          {paginatedData.map((row) => {
            const id = getRowKey(row);
            return (
              <Table.Row key={id}>
                {selectable && (
                  <Table.Cell>
                    <Switch
                      selected={selectedIds.has(id)}
                      onClick={() => handleRowSelect(id)}
                      appearance="checkbox"
                    />
                  </Table.Cell>
                )}
                {columns.map((column) => (
                  <Table.Cell
                    key={String(column.key)}
                    align={column.align}
                  >
                    {renderCell(column, row)}
                  </Table.Cell>
                ))}
              </Table.Row>
            );
          })}
        </Table.Body>
      </Table>

      {paginated && totalPages > 1 && (
        <TableFooter>
          <RowsPerPageSelect>
            <span>Rows per page:</span>
            <Select value={String(pageSize)} onChange={handlePageSizeChange}>
              {pageSizeOptions.map((option) => (
                <Select.Option key={option} label={String(option)} value={String(option)} />
              ))}
            </Select>
          </RowsPerPageSelect>

          <PageInfo>
            Showing {(currentPage - 1) * pageSize + 1} -{' '}
            {Math.min(currentPage * pageSize, sortedData.length)} of {sortedData.length}
          </PageInfo>

          <Paginator
            current={currentPage}
            totalPages={totalPages}
            onChange={handlePageChange}
          />
        </TableFooter>
      )}
    </TableContainer>
  );
}

export default DataTable;
